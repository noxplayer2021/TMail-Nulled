<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TMail;
use Exception;

class APIController extends Controller {

    public function domains($key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            return Setting::pick('domains');
        } else {
            return abort(401);
        }
    }

    public function email($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $split = explode('@', $email);
                    return TMail::createCustomEmail($split[0], $split[1]);
                } catch (Exception $e) {
                    return TMail::generateRandomEmail(false);
                }
            } else {
                return TMail::generateRandomEmail(false);
            }
        } else {
            return abort(401);
        }
    }

    public function messages($email = '', $key = '') {
        $keys = Setting::pick('api_keys');
        if (in_array($key, $keys)) {
            if ($email) {
                try {
                    $data = [];
                    $mailbox = TMail::connectMailBox();
                    $messages = $mailbox->query()->to($email)->leaveUnread()->get();
                    $unseen = 0;
                    foreach ($messages as $message) {
                        $receivers = $message->getTo();
                        $receiver_found = false;
                        foreach ($receivers as $receiver) {
                            if ($receiver->mail == $email) {
                                $receiver_found = true;
                                break;
                            }
                        }
                        if ($receiver_found === false) {
                            continue;
                        }
                        $new = [
                            'subject' => $message->getSubject(),
                            'sender_name' => $message->getSender()[0]->personal,
                            'sender_email' => $message->getSender()[0]->mail,
                            'date' => $message->getDate()->format('d M Y h:i A'),
                            'datediff' => $message->getDate()->diffForHumans(),
                            'id' => $message->getMsgn(),
                            'content' => $message->hasHTMLBody() ? str_replace('<a', '<a target="blank"', $message->getHTMLBody()) : str_replace(
                                '<a',
                                '<a target="blank"',
                                str_replace(array("\r\n", "\n"), '<br/>', $message->getTextBody())
                            ),
                            'attachments' => []
                        ];
                        if (!$new['sender_name']) {
                            $new['sender_name'] = 'Unknown';
                        }
                        if ($message->hasAttachments()) {
                            $attachments = $message->getAttachments();
                            $directory = './tmp/attachments/' . $message->getMsgn() . '/';
                            is_dir($directory) ?: mkdir($directory, 0777, true);
                            foreach ($attachments as $id => $attachment) {
                                if (!file_exists($directory . $attachment->name)) {
                                    $attachment->save($directory);
                                }
                                if ($attachment->name !== 'undefined') {
                                    $url = env('APP_URL') . str_replace('./', '/', $directory . $attachment->name);
                                    if (str_contains($new['content'], $id)) {
                                        $new['content'] = str_replace('cid:' . $id, $url, $new['content']);
                                    }
                                    array_push($new['attachments'], [
                                        'file' => $attachment->name,
                                        'url' => $url
                                    ]);
                                }
                            }
                        }
                        if ($message->getFlags()->get('seen', null) === null) {
                            $unseen++;
                            $message->setFlag('Seen');
                        }
                        $data[$new['id']] = $new;
                    }
                    TMail::incrementMessagesStats($unseen);
                    return $data;
                } catch (\Exception $e) {
                    return abort(500);
                }
            } else {
                return abort(204);
            }
        } else {
            return abort(401);
        }
    }
}
