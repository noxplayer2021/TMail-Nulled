<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use App\Models\Meta;

class TMail extends Model {

    public static function connectMailBox($imap = null) {
        if ($imap === null) {
            $imap = config('app.settings.imap');
        }
        $client = \Webklex\IMAP\Facades\Client::make($imap);
        $client->connect();
        return $client->getFolder('INBOX');
    }

    public static function getEmail($generate = false) {
        if (Cookie::has('email')) {
            return Cookie::get('email');
        } else {
            return $generate ? TMail::generateRandomEmail() : null;
        }
    }

    public static function getEmails() {
        if (Cookie::has('emails')) {
            return unserialize(Cookie::get('emails'));
        } else {
            return [];
        }
    }

    public static function setEmail($email) {
        Cookie::queue('email', $email, 43800);
    }

    public static function removeEmail($email) {
        $emails = TMail::getEmails();
        $key = array_search($email, $emails);
        if ($key !== false) {
            array_splice($emails, $key, 1);
            if (count($emails) > 0) {
                TMail::setEmail($emails[0]);
                Cookie::queue('emails', serialize($emails), 43800);
            } else {
                Cookie::queue('email', '', -1);
                Cookie::queue('emails', serialize([]), -1);
            }
        }
    }

    /**
     * this method is used to save emails
     */

    private static function storeEmail($email) {
        Cookie::queue('email', $email, 43800);
        $emails = Cookie::has('emails') ? unserialize(Cookie::get('emails')) : [];
        if (array_search($email, $emails) === false) {
            TMail::incrementEmailStats();
            array_push($emails, $email);
            Cookie::queue('emails', serialize($emails), 43800);
        }
    }

    public static function createCustomEmailFull($email) {
        $data = explode('@', $email);
        $username = $data[0];
        $domain = $data[1];
        return TMail::createCustomEmail($username, $domain);
    }

    public static function createCustomEmail($username, $domain) {
        $username = \str_replace('[^a-zA-Z0-9]', '', strtolower($username));
        $forbidden_ids = config('app.settings.forbidden_ids');
        if (in_array($username, $forbidden_ids)) {
            return TMail::generateRandomEmail(true);
        }
        $domains = config('app.settings.domains');
        if (in_array($domain, $domains)) {
            $email = $username . '@' . $domain;
            TMail::storeEmail($email);
            return $email;
        } else {
            $email = $username . '@' . $domains[0];
            TMail::storeEmail($email);
            return $email;
        }
    }

    /**
     * Stats Handling Functions
     */
    public static function incrementEmailStats($count = 1) {
        Meta::incrementEmailIdsCreated($count);
    }

    public static function incrementMessagesStats($count = 1) {
        Meta::incrementMessagesReceived($count);
    }

    public static function generateRandomEmail($store = true) {
        $tmail = new TMail;
        $email = $tmail->generateRandomUsername() . '@' . $tmail->getRandomDomain();
        if ($store) {
            TMail::storeEmail($email);
        }
        return $email;
    }

    private function generateRandomUsername() {
        $start = config('app.settings.random.start', 0);
        $end = config('app.settings.random.end', 0);
        if ($start == 0 && $end == 0) {
            return $this->generatePronounceableWord();
        }
        return $this->generatedRandomBetweenLength($start, $end);
    }

    protected function generatedRandomBetweenLength($start, $end) {
        $length = rand($start, $end);
        return $this->generateRandomString($length);
    }

    private function getRandomDomain() {
        $domains = config('app.settings.domains');
        $count = count($domains);
        return $count > 0 ? $domains[rand(1, $count) - 1] : '';
    }

    private function generatePronounceableWord() {
        $c  = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v  = 'aeiou';              //vowels
        $a  = $c . $v;                //both
        $random = '';
        for ($j = 0; $j < 2; $j++) {
            $random .= $c[rand(0, strlen($c) - 1)];
            $random .= $v[rand(0, strlen($v) - 1)];
            $random .= $a[rand(0, strlen($a) - 1)];
        }
        return $random;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
