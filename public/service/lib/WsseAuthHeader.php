<?php
class WsseAuthHeader extends SoapHeader {


    private $__wssNamespace = '';


    private $__userName = '';


    private $__userPassword = '';


    private $__securityData;



    public function __construct($userName = '', $userPassword = '', $wssNamespace = '') {
        // Define parameters
        $tmp_namespace        = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $this->__wssNamespace = ( !empty($wssNamespace) ) ? $wssNamespace : $tmp_namespace;
        $this->__userName     = $userName;
        $this->__userPassword = $userPassword;

        // Build header body
        self::__buildHeaderBody();

        // Build SOAP header
        parent::__construct( $this->__wssNamespace, 'Security', $this->__securityData, true );
    }


    private function __buildHeaderBody(){
        // Set header body
        $auth = new stdClass();
        $auth->Username = new SoapVar($this->__userName,
            XSD_STRING,
            NULL,
            $this->__wssNamespace,
            NULL,
            $this->__wssNamespace);
        $auth->Password = new SoapVar($this->__userPassword,
            XSD_STRING,
            NULL,
            $this->__wssNamespace,
            NULL,
            $this->__wssNamespace);

        $username_token = new stdClass();
        $username_token->UsernameToken = new SoapVar($auth,
            SOAP_ENC_OBJECT,
            NULL,
            $this->__wssNamespace,
            'UsernameToken',
            $this->__wssNamespace);

        $this->__securityData = new SoapVar(new SoapVar($username_token,
            SOAP_ENC_OBJECT,
            NULL,
            $this->__wssNamespace,
            'UsernameToken',
            $this->__wssNamespace),
            SOAP_ENC_OBJECT,
            NULL,
            $this->__wssNamespace,
            'Security',
            $this->__wssNamespace);

        return;
    }

}