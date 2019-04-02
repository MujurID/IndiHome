<?php

/**
* This class made with free time and for FUN? only
* Saya tidak bertanggung jawab atas sehala kerugian yang ditimbulkan
* atas yang terjadi dikarenakan class ini
* 
* This code is in no way affiliated with, authorized, maintained,
* sponsored or endorsed by TELKOM INDONESIA (Company) or any of its
* affiliates or subsidiaries. Use at your own risk.
*
* Email : ?? :)
* Automation Script - Askaeks Technology (2017)
**/

class IndiHome {
    use Request;
    private $emailAPI_DomainList = 'http://api.temp-mail.ru/request/domains/format/json/';
    private $emailAPI_EmailList = 'http://api.temp-mail.ru/request/mail/id/%s/format/json/';
    private $profileAPI = 'https://randomuser.me/api/';
    private $indiHome_Subcribe = 'https://indihome.co.id/subscribe';
    private $indiHome_AjaxLogin = 'https://indihome.co.id/ajax/login?profile=kosong&fullname=%s&redirect=https://my.indihome.co.id/registrasi-indihome';
    private $indiHome_Registration = 'https://my.indihome.co.id/registrasi-indihome';
    private $indiHome_Inbox = 'https://my.indihome.co.id/inbox';
    private $domainList = array();
    private $debug;
    public function run($count, $debug = false) {
        # get email list for first use
        $this->debug = $debug;
        $this->init()->getEmailDomainList();
        for($i=0;$i<$count;$i++)
            echo $this->subcribe();
echo "<br/>";
echo "<br/>";
    }
    public function subcribe() {
        $details = $this->generateSubcribeDetails();
        $this->http($this->indiHome_Subcribe)->isPOST($details)->http($this->indiHome_Subcribe);
        # DEBUG
        if($this->debug) file_put_contents("a.txt", $this->_response);
        if(strpos($this->_response, 'html') or empty($this->_header['location'][0])) return 'Cannot subcribe using your details!';
        $location = $this->_header['location'][0];
        # DEBUG
        if($this->debug) 
echo $location . "\n";
echo "<br/>";
echo "<br/>";
        $this->http($location)->wait(5)->isPOST($this->getOTPPassword($details))->http($location);
        # DEBUG
        if($this->debug) 
echo json_encode($this->_header) . "\n";
echo "<br/>";
echo "<h1>";
echo "<br/>";
        if(!empty($this->_header['location'][0])) {
            $inbox = $this->http(sprintf($this->indiHome_AjaxLogin, $details['txtNama']))->http($this->indiHome_Registration)->http($this->indiHome_Inbox)->_response;
            preg_match_all("/style=\"color:black\">(.*)<\/a>/", $inbox, $out);
            if(empty($out[1][0])) throw new Exception("Umm... inbox is empty?");
            return $out[1][0];
        } else throw new Exception("Can't submit OTP Password, brauh!");
    }
    private function generateSubcribeDetails() {
        $request = array(
            'ci_csrf_token' => '',
            'txtNama' => '',
            'txtEmail' => '',
            'txtPassword' => '',
            'txtConfPassword' => '',
            'txtNoHP' => '',
            'txtNoHPalt' => '',
            'jeniskelamin' => ''
        );
        $profile = json_decode($this->http($this->profileAPI)->_response);
        $randomNumber = function($length) {
            return join('', array_map(function($value) { return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9); }, range(1, $length)));
        };
        if(empty($profile)) throw new Exception("Can't get profile information.");
        $result = $profile->results[0];
        $request['txtNama'] = $result->name->first . ' ' . $result->name->last;
        $request['txtEmail'] = str_replace(' ', '', $request['txtNama']) . mt_rand() . $this->domainList[mt_rand(0, count($this->domainList) - 1)];
        $request['txtPassword'] = $result->login->username;
        $request['txtConfPassword'] = $request['txtPassword'];
        $request['txtNoHP'] = '082' . $randomNumber(9);
        $request['jeniskelamin'] = (mt_rand(0, 1) == 1) ? 'pria' : 'wanita';
        return $request;
    }
    private function getOTPPassword($profile) {
        preg_match_all("/name=\"csrf_test_name\" value=\"(.*)\"/", $this->_response, $out);
        if(empty($out[1][0])) throw new Exception("No security (CSRF) found, are you okay?");
        $CSRF = $out[1][0];
        $emailResponse = json_decode($this->http(sprintf($this->emailAPI_EmailList, md5($profile['txtEmail'])))->_response);
        if(empty($emailResponse)) throw new Exception("Failed connect to email services.");
        $email = "";
        foreach($emailResponse as $emails) {
            if($emails->mail_subject == 'Verifikasi MyIndihome') {
                $email = $emails->mail_text_only; break;
            }
        }
        preg_match_all("/myindihome anda adalah ([0-9]+[^\ <]+)/", $email, $out);
        if(empty($out[1][0])) throw new Exception("Email found but OTP can not found, are you okay?");
        $OTP = $out[1][0];
        if(strlen($OTP) <> 4 OR !is_numeric($OTP)) throw new Exception("OTP has not valid format? ummm.");
        $request = array(
            'csrf_test_name' => $CSRF,
            'txtKodeOTP' => $OTP
        );
        # DEBUG
        if($this->debug) 
echo json_encode($request) . "\n";
echo "<br/>";
echo "<br/>";
        return $request;
    }
    private function getEmailDomainList() {
        $this->domainList = json_decode($this->http($this->emailAPI_DomainList)->_response);
        if(count($this->domainList) == 0) throw new Exception("Sorry, there is no domain list!"); return $this;
    }
}

trait Request {
    private $_post, $_response, $_header, $_error, $_KUKIS;
    protected function isPOST(array $post_data) {
        $this->_post = http_build_query($post_data); return $this;
    }
    private function resetRequest() {
        $this->_post = null;
        $this->_error = null;
    }
    protected function init() {
        $this->_KUKIS = tempnam('/tmp','cookie.txt'); return $this;
    }
    protected function wait($c = 3) {
        sleep($c); return $this;
    }
    protected function http($web) {
        $headers = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $web);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_KUKIS); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_KUKIS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
          function($curl, $header) use (&$headers)
          {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2)
              return $len;
            $name = strtolower(trim($header[0]));
            if (!array_key_exists($name, $headers)) $headers[$name] = [trim($header[1])];
            else $headers[$name][] = trim($header[1]);
            return $len;
          }
        );
        if($this->_post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_post);
        }
        $response = curl_exec($ch);
        if(curl_error($ch)) throw new Exception(curl_error($ch));
        $this->_response = $response;
        $this->_header = $headers;
        curl_close($ch);
        $this->resetRequest();
        return $this;
    }
}
