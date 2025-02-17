<?php
/**
 * File containing the ezcDbInstance class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Database
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Holds database instances for global access throughout an application.
 *
 * It is common for many application to use only one or very few database connections.
 * This class holds a single database connection name or a list of databases
 * identified by a handle. The database connections can be retrieved from anywhere
 * within your code through the static methods.
 * This eliminates the need to pass the connection handle around.
 *
 * Typical usage example:
 * <code>
 * $db = ezcDbFactory::create( $dbparams );
 * ezcDbInstance::set( $db );
 *
 * // ...
 *
 * $db = ezcDbInstance::get();
 * </code>
 *
 * More complex example, with two connections, having identifiers (for convenience):
 * <code>
 * $mydb = ezcDbFactory::create( $mysql_dbparams );
 * $pgdb = ezcDbFactory::create( $pgsql_dbparams );
 *
 * ezcDbInstance::set( $mydb, 'my' );
 * ezcDbInstance::set( $pgdb, 'pg' );
 * ezcDbInstance::chooseDefault( 'my' );
 *
 * // ...
 *
 * $mydb = ezcDbInstance::get( 'my' ); // returns the mysql instance
 * $pgdb = ezcDbInstance::get( 'pg' ); // returns the pgsql instance
 * $mydb = ezcDbInstance::get();  // returns the mysql instance which is default
 * </code>
 *
 * @package Database
 * @version //autogentag//
 * @mainclass
 */
class ezcDbInstance
{
    /**
     * Identifier of the instance that will be returned
     * when you call get() without arguments.
     *
     * @see ezcDbInstance::get()
     * @var string
     */
    static private $DefaultInstanceIdentifier = false;

    /**
     * Holds the database instances.
     *
     * Example:
     * <code>
     * array( 'mysql1' => [object],
     *        'mysql2' => [object],
     *        'oracle' => [object] )
     * </code>
     *
     * @var array(string=>ezcDbHandler)
     */
    static private $Instances = array();

    /**
     * Returns the database instance $identifier.
     *
     * If $identifier is ommited the default database instance
     * specified by chooseDefault() is returned.
     *
     * @throws ezcDbHandlerNotFoundException if the specified instance is not found.
     * @param string $identifier
     * @return ezcDbHandler
     */
    public static function get( $identifier = false )
    {
        if ( $identifier === false && self::$DefaultInstanceIdentifier )
        {
            $identifier = self::$DefaultInstanceIdentifier;
        }

        if ( !isset( self::$Instances[$identifier] ) )
        {
            // The DatabaseInstanceFetchConfig callback should return an
            // ezcDbHandler object which will then be set as instance.
            $ret = ezcBaseInit::fetchConfig( 'ezcInitDatabaseInstance', $identifier );
            if ( $ret === null )
            {
                throw new ezcDbHandlerNotFoundException( $identifier );
            }
            else
            {
                self::set( $ret, $identifier );
            }
        }

        return self::$Instances[$identifier];
    }

    /**
     * Returns the identifiers of the registered database instances.
     *
     * @return string[]
     */
    public static function getIdentifiers()
    {
        return array_keys( self::$Instances );
    }

    /**
     * Adds the database handler $db to the list of known instances.
     *
     * If $identifier is specified the database instance can be
     * retrieved later using the same identifier.
     *
     * @param ezcDbHandler $db
     * @param string $identifier the identifier of the database handler
     * @return void
     */
    public static function set( ezcDbHandler $db, $identifier = false )
    {
        self::$Instances[$identifier] = $db;
    }

    /**
     * Sets the database $identifier as default database instance.
     *
     * To retrieve the default database instance
     * call get() with no parameters..
     *
     * @see ezcDbInstance::get().
     * @param string $identifier
     * @return void
     */
    public static function chooseDefault( $identifier )
    {
        self::$DefaultInstanceIdentifier = $identifier;
    }

    /**
     * Resets the default instance holder.
     *
     * @return void
     */
    public static function resetDefault()
    {
        self::$DefaultInstanceIdentifier = false;
    }

    /**
     * Resets this object to its initial state.
     *
     * The list of instances will be emptied and
     * {@link resetDefault()} will be called.
     *
     * @return void
     */
    public static function reset()
    {
        self::$Instances = array();
        self::resetDefault();
    }
}

?>
