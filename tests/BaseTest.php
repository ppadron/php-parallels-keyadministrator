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

require_once 'PHPUnit/Framework/TestCase.php';

class BaseTest extends PHPUnit_Framework_TestCase
{
    private function _buildFakeRequest($params)
    {
        return array_merge($this->_auth, $params);
    }

    private function _getXmlRpcMock($apiMethod, $returnValue, $xmlRpcParams = array())
    {
        $mock = $this->getMock('XML_RPC2_Client', array('__call', 'remoteCall___'), array(), '', false);

        $params = $this->_buildFakeRequest($xmlRpcParams);

        $mock->expects($this->once())
             ->method('__call')
             ->with($apiMethod, $params)
             ->will($this->returnValue($returnValue));

        return $mock;

    }
}