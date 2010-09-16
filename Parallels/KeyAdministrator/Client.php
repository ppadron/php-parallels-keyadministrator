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

class Parallels_KeyAdministrator_Client
    extends Parallels_KeyAdministrator_Common
{
        
    const API_ERROR_LOGIN_ALREADY_EXISTS        = 251;
    const API_ERROR_INCORRECT_KEY_FORMAT        = 410;
    const API_ERROR_INCORRECT_LOGIN_FORMAT      = 431;
    const API_ERROR_INCORRECT_PASSWORD_FORMAT   = 432;
    const API_ERROR_INCORRECT_FIRST_NAME_FORMAT = 433;
    const API_ERROR_INCORRECT_LAST_NAME_FORMAT  = 434;
    const API_ERROR_INCORRECT_COMPANY_FORMAT    = 435;
    const API_ERROR_INCORRECT_ADDRESS_FORMAT    = 436;
    const API_ERROR_INCORRECT_EMAIL_FORMAT      = 437;
    const API_ERROR_INCORRECT_PHONE_FORMAT      = 438;
    const API_ERROR_INCORRECT_FAX_FORMAT        = 439;
    const API_ERROR_INCORRECT_CITY_FORMAT       = 440;
    const API_ERROR_INCORRECT_ZIP_CODE_FORMAT   = 441;
    const API_ERROR_INCORRECT_STATE_FORMAT      = 442;
    const API_ERROR_INCORRECT_COUNTRY_FORMAT    = 443;
    const API_ERROR_INCORRECT_LANGUAGE_FORMAT   = 444;
    const API_ERROR_FIRST_NAME_TOO_LONG         = 447;
    const API_ERROR_LAST_NAME_TOO_LONG          = 448;
    const API_ERROR_EMAIL_TOO_LONG              = 450;
    const API_ERROR_NO_SEARCH_PARAMETER_DEFINED = 451;

    public function __construct($login, $passwd, $apiEndpoint)
    {
        parent::__construct($login, $passwd, $url);

        if (!empty($clientLogin)) {
            $this->clientLogin = $clientLogin;
        }

    }

    /**
     * Generates a new API password for the client
     *
     * @return string New password
     */
    public function generateNewPassword()
    {
        $result = $this->sendRequest('partner10.generateNewPassword');

        if ($result['resultCode'] == self::API_SUCCESS) {
            return $result['newPassword'];
        } else {
            throw new Services_Parallels_KA_Exception(
                $result['resultCode'],
                $result['resultDesc']
            );
        }
    }

    /**
     * Checks if the specified login credentials are valid
     *
     * @return boolean
     */
    public function isValidLogin()
    {
        $result = $this->sendRequest('partner10.validateLogin');

        switch ($result['resultCode']) {

        case self::API_SUCCESS:
            return true;
        case self::API_ERROR_INTERNAL_ERROR:
            throw new Services_Parallels_KA_Exception(
                self::API_ERROR_INTERNAL_ERROR,
                $result['resultDesc']
            );

        case self::API_ERROR_AUTHORIZATION_FAILED:
        case self::API_ERROR_INCORRECT_AUTHORIZATION_INFO:
        default:
            return false;
        }
    }
}