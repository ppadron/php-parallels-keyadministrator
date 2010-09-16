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

require_once 'BaseTest.php';
require_once 'Parallels/KeyAdministrator.php';

class KeyTest extends BaseTest
{
    
    public function setUp()
    {
        $this->ka = new Parallels_KeyAdministrator(
            PARALLELS_KA_LOGIN, PARALLELS_KA_PASSWORD, PARALLELS_KA_URL
        );
    }

    public function testGivenCorrectDataShouldCreateKey()
    {
        $key = $this->ka->key->create(PARALLELS_KA_CLIENT, 'PLESK_80_PLUS');

        $this->assertArrayHasKey('key_number',           $key);
        $this->assertArrayHasKey('update_timestamp',     $key);
        $this->assertArrayHasKey('expiration_timestamp', $key);
        $this->assertArrayHasKey('additional_keys',      $key);

        return $key['key_number'];
    }

    public function testShouldListAvailableKeyTypesAndFeatures()
    {
        $return = $this->ka->key->getAvailableKeyTypesAndFeatures(PARALLELS_KA_CLIENT);
        
        $this->assertArrayHasKey('features',  $return);
        $this->assertType('array', $return['features']);

        $this->assertArrayHasKey('key_types', $return);
        $this->assertType('array', $return['key_types']);
    }

    /**
     * @depends testGivenCorrectDataShouldCreateKey
     */
    public function testGivenExistingKeyShouldListAvailableUpgrades($keyNumber)
    {
        $return = $this->ka->key->getAvailableUpgrades($keyNumber);
        
        $this->assertType('array', $return);
        $this->assertTrue(in_array('1YR_PREMIUM_SUPPORT_PACK', $return));
    }

    /**
     * @depends testGivenCorrectDataShouldCreateKey
     */
    public function testGivenExistingKeyNumberShouldGetKeyInfo($keyNumber)
    {
        $return = $this->ka->key->get($keyNumber);

        $this->assertType('array', $return);
        $this->assertArrayHasKey('expires_at', $return);
        
        $this->assertType('boolean', $return['is_terminated']);
        $this->assertType('boolean', $return['is_problem']);
        $this->assertType('string',  $return['billing_type']);
    }

    public function testIfKeyIsUsedInPleskShouldReturnItsUsage()
    {
        if (!defined('PARALLELS_KA_KEY')) {
            $this->markTestSkipped();
        }
    }

    /**
     * @depends testGivenCorrectDataShouldCreateKey
     */
    public function testIfKeyIsNotUsedInPleskShouldThrowException($keyNumber)
    {
        $this->expectedException     = 'Parallels_KeyAdministrator_Exception';
        $this->expectedExceptionCode = 233;
        $return = $this->ka->key->getLastPleskUsageInfo($keyNumber);
    }

    /**
     * @depends testGivenCorrectDataShouldCreateKey
     */
    public function testShouldUpgradeToBillingTypeAsPurchase($keyNumber)
    {
        $feature = '1YR_PREMIUM_SUPPORT_PACK';

        $return = $this->ka->key->upgrade(
            $keyNumber, $feature, 'PURCHASE'
        );
        
        $this->assertArrayHasKey('updated_at', $return);
        $this->assertArrayHasKey('key_number', $return);

        return array('key_number' => $keyNumber, 'feature' => $feature);
    }

    /**
     * @depends testShouldUpgradeToBillingTypeAsPurchase
     */
    public function testShouldNotThrowExceptionIfAlreadyHasFeature($keyInfo)
    {
        $return = $this->ka->key->upgrade(
            $keyInfo['key_number'], $keyInfo['feature']
        );
        
        $this->assertArrayHasKey('updated_at', $return);
        $this->assertArrayHasKey('key_number', $return);
    }

    /**
     * @depends testGivenCorrectDataShouldCreateKey
     */
    public function testShouldBeAbleToAddNoteToKey($keyNumber)
    {
        $note = 'This is just a test.';
        
        $this->assertTrue(
            $this->ka->key->addNoteToKey($keyNumber, $note)
        );
    }
}