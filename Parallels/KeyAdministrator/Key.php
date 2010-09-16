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

require_once 'Parallels/KeyAdministrator/Common.php';

class Parallels_KeyAdministrator_Key extends Parallels_KeyAdministrator_Common
{
    
    protected function parseFeatureList(array $featureList)
    {
        if (empty($featureList)) {
            return array();
        }

        $return = array();

        foreach ($featureList as $feature) {
            $return[$feature['apiName']] = $feature['name'];
        }

        return $return;
    }

    protected function parseKeyInfo(array $info)
    {
        $features = $this->parseFeatureList($info['features']);

        return array(
            'key_number'      => $info['keyNumber'],
            'created_at'      => $info['createDate']->timestamp,
            'expires_at'      => $info['expirationDate']->timestamp,
            'client_id'       => $info['clientId'],
            'properties'      => $info['properties'],
            'features'        => $features,
            'product_family'  => $info['productFamily'],
            'key_description' => $info['keyType'],
            'updated_at'      => $info['updateDate']->timestamp,
            'additional_keys' => $info['additionalKeys'],
            'is_terminated'   => $info['terminated'],
            'is_problem'      => $info['problem'],
            'sus_support'     => $info['susAndSupportInfo'],
            'billing_type'    => $info['billingType'],
            'key_type'        => $info['type'],
            'ip_address'      => $info['boundIPAddress']
        );
    }

    /**
     * Creates a new key
     *
     * @param string $keyOwner
     * @param string $keyType
     * @param array  $upgradePlans
     * @param array  $serverAddresses
     *
     * @return array
     */
    public function create($keyOwner, $keyType, $upgradePlans = array(),
            $serverAddresses = array())
    {
        if (empty($serverAddresses)) {
            $serverAddresses = array('ips' => array(), 'macs' => array());
        }

        $result = $this->sendRequest(
            'partner10.createKey',
            $serverAddresses,
            $keyOwner,
            $keyType,
            $upgradePlans
        );

        return array(
            'key_number'           => $result['mainKeyNumber'],
            'update_timestamp'     => $result['updateDate']->timestamp,
            'expiration_timestamp' => $result['expirationDate']->timestamp,
            'additional_keys'      => $result['additionalKeysNumbers']
        );
    }

    /**
     * Returns all available key types and features for a specific client
     *
     * @param string $client
     *
     * @return array
     */
    public function getAvailableKeyTypesAndFeatures($client)
    {
        $result = $this->sendRequest(
            'partner10.getAvailableKeyTypesAndFeatures', $client
        );

        return array(
            'key_types' => $result['keyTypes'],
            'features'  => $result['features']
        );
    }

    /**
     * Returns all available upgrade plans for a specific license
     *
     * @param string $keyNumber
     * @return array Available upgrades
     */
    public function getAvailableUpgrades($keyNumber)
    {
        $result = $this->sendRequest('partner10.getAvailableUpgrades', $keyNumber);
        return $result['upgradePlans'];
    }

    /**
     * Upgrades the license to the specified plan
     *
     * @param string $keyNumber   Key number
     * @param string $upgradePlan Name of the upgrade plan
     * @param string $billingType Billing type
     *
     * @return array
     */
    public function upgrade($keyNumber, $upgradePlan, $billingType = null)
    {
        if (empty($billingType)) {
            $result = $this->sendRequest(
                'partner10.upgradeKey', $keyNumber, $upgradePlan
            );
        } else {
            $result = $this->sendRequest(
                'partner10.upgradeKey', $keyNumber, $upgradePlan, $billingType
            );
        }

        return array(
            'key_number'      => $result['keyNumber'],
            'expires_at'      => $result['expirationDate']->timestamp,
            'updated_at'      => $result['updateDate']->timestamp,
            'sus_expires_at'  => isset($result['susDate']) ?
                $result['susDate']->timestamp : '',
            'product_key'     => isset($result['productKey']) ?
                $result['productKey'] : '',
        );

    }

    /**
     * Returns key metadata
     *
     * @param string $keyNumber
     */
    public function get($keyNumber)
    {
        $result = $this->sendRequest('partner10.getKeyInfo', $keyNumber);
        return $this->parseKeyInfo($result['keyInfo']);
    }

    /**
     * Finds a key by a specified IP Address
     * 
     * @param string $ipAddress IP address
     * 
     * @return void
     */
    public function findByIp($ipAddress)
    {
        print_r($this->sendRequest('partner10.getKeysInfoByIP', $ipAddress));
    }

    /**
     * Returns the last reported server usage information with the license
     * 
     * The following information will be returned in the array:
     * 
     * key_number       key number
     * resellers        number of resellers in the server
     * ip_addresses     all server ip addresses
     * product_version  product version
     * clients          number of clients in the server
     * domains          number of domains in the server
     * updated_at       timestamp of the date when this information was updated
     * 
     * @return array Associative array containing usage information
     */
    public function getLastPleskUsageInfo($keyNumber)
    {
        $result = $this->sendRequest(
            'partner10.getLastPleskUsageInfo', $keyNumber
        );

        $usageInfo = array();

        $usageInfo['key_number']      = $result['keyNumber'];
        $usageInfo['resellers']       = $result['lastUsageInfo']['resellers'];
        $usageInfo['ip_addresses']    = $result['lastUsageInfo']['ips'];
        $usageInfo['product_version'] = $result['lastUsageInfo']['productVersion'];
        $usageInfo['clients']         = $result['lastUsageInfo']['clients'];
        $usageInfo['domains']         = $result['lastUsageInfo']['domains'];
        $usageInfo['updated_at']      = $result['lastUsageInfo']['lastDate']->timestamp;

        return $usageInfo;
    }

    /**
     * Adds a note (text comment) to a key that will be visible in the KA panel
     *
     * @param string $keyNumber
     * @param string $note
     *
     * @return boolean
     */
    public function addNoteToKey($keyNumber, $note)
    {
        $result = $this->sendRequest('partner10.addNoteToKey', $keyNumber, $note);

        // here we return true directly because if something wrong happenned,
        // an exception will already be thrown
        return true;
    }

}