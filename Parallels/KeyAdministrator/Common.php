<?php
/**
 * +-----------------------------------------------------------------------+
 * | Copyright (c) 2010, W3P Projetos Web                                  |
 * | All rights reserved.                                                  |
 * |                                                                       |
 * | Redistribution and use in source and binary forms, with or without    |
 * | modification, are permitted provided that the following conditions    |
 * | are met:                                                              |
 * |                                                                       |
 * | o Redistributions of source code must retain the above copyright      |
 * |   notice, this list of conditions and the following disclaimer.       |
 * | o Redistributions in binary form must reproduce the above copyright   |
 * |   notice, this list of conditions and the following disclaimer in the |
 * |   documentation and/or other materials provided with the distribution.|
 * | o The names of the authors may not be used to endorse or promote      |
 * |   products derived from this software without specific prior written  |
 * |   permission.                                                         |
 * |                                                                       |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
 * | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
 * | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
 * | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
 * | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
 * | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
 * | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
 * | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
 * | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 * | Author: Pedro Padron <ppadron@w3p.com.br>                             |
 * +-----------------------------------------------------------------------+
 *
 * PHP version 5
 *
 * @package  Parallels_KeyAdministrator
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  New BSD License http://www.opensource.org/licenses/bsd-license.php
 */

require_once 'XML/RPC2/Client.php';
require_once 'Parallels/KeyAdministrator/Exception.php';

class Parallels_KeyAdministrator_Common
{
    const API_SUCCESS                            = 100;
    const API_NO_RESULTS                         = 101;
    const API_ERROR_AUTHORIZATION_FAILED         = 200;
    const API_ERROR_METHOD_ACCESS_DENIED         = 201;
    const API_ERROR_OBJECT_ACCESS_DENIED         = 202;
    const API_ERROR_INTERNAL_ERROR               = 300;
    const API_ERROR_INCORRECT_AUTHORIZATION_INFO = 400;
    const API_ERROR_INCORRECT_SERVER_INFO        = 401;
    
    /**
     * XML-RPC entry point
     *
     * @var string
     */
    protected $apiEndpoint = 'https://ka.parallels.com:7050';

    /**
     * API login
     *
     * @var string
     */
    protected $login;

    /**
     * API password
     *
     * @var string
     */
    protected $passwd;

    /**
     * XML RPC client
     *
     * @var XML_RPC2_Client
     */
    protected $xmlrpc;

    /**
     * Class constructor
     *
     * @param string $login       API login
     * @param string $passwd      API password
     * @param string $apiEndpoint XML-RPC API endpoint
     */
    public function __construct($login, $passwd, $apiEndpoint = null)
    {
        if (!empty($apiEndpoint)) {
            $this->apiEndpoint = $apiEndpoint;
        }

        $this->login  = $login;
        $this->passwd = $passwd;
    }

    /**
     * Defines the XML-RPC client that will be used. Useful for testing using mocks.
     *
     * @param stdClass $xmlrpc XML-RPC client
     *
     * @return void
     */
    public function setXmlRpcClient($xmlrpc)
    {
        $this->xmlrpc = $xmlrpc;
    }

    protected function getXmlRpcClient()
    {
        // Do we have a XML-RPC client already? If not, defaults to XML_RPC2
        if (!isset($this->xmlrpc)) {
            $options = array('sslverify' => false);
            $this->setXmlRpcClient(XML_RPC2_Client::create(
                $this->apiEndpoint, $options
            ));
        }
        return $this->xmlrpc;        
    }

    /**
     * Sends the request to the XML-RPC server
     *
     * @param string $method Name of the XML-RPC method that will be called
     *
     * @throws Services_Parallels_KA_Exception
     *
     * @return array
     */
    protected function sendRequest($method)
    {
        $xmlrpc = $this->getXmlRpcClient();

        // Obtaining all the parameters
        $methodParams = func_get_args();

        // Removing the first parameter (method name)
        array_shift($methodParams);

        $rpcParams[] = array('login' => $this->login, 'password' => $this->passwd);

        foreach ($methodParams as $param) {
            $rpcParams[] = $param;
        }

        $result = $xmlrpc->__call($method, $rpcParams);

        // Result codes can be the same for different actions in different contexts.
        // This way, the only case covered here is success result code range (1xx).
        // If any other code is returned, an exception will be thrown, and must be
        // handled by who called this method.
        if ($result['resultCode'] >= 200) {
            throw new Parallels_KeyAdministrator_Exception(
                $result['resultDesc'],
                $result['resultCode']
            );
        }

        return $result;

    }

}