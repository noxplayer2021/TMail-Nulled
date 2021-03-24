<?php

namespace App\Http\Livewire\Frontend;

use Livewire\Component;
use App\Models\TMail;

class App extends Component {

    public $messages = [];
    public $error = '';
    public $email;
    public $initial;

    protected $listeners = ['fetchMessages' => 'fetch'];

    public function mount() {
        $this->email = TMail::getEmail(true);
        $this->initial = false;
    }

    public function fetch() {
        try {
            $this->messages = [];
            $mailbox = TMail::connectMailBox();
            $messages = $mailbox->query()->to($this->email)->leaveUnread()->get();
            $unseen = 0;
            foreach ($messages as $message) {
                $receivers = $message->getTo();
                $receiver_found = false;
                foreach ($receivers as $receiver) {
                    if ($receiver->mail == $this->email) {
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
                    $this->dispatchBrowserEvent('showNewMailNotification', $new);
                    $unseen++;
                    $message->setFlag('Seen');
                }
                array_push($this->messages, $new);
            }
            TMail::incrementMessagesStats($unseen);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        $this->dispatchBrowserEvent('stopLoader');
        $this->dispatchBrowserEvent('loadDownload');
        $this->initial = true;
    }

    public function delete($messageId) {
        $mailbox = TMail::connectMailBox();
        $message = $mailbox->query()->getMessage($messageId);
        $message->delete(true);
        unset($this->messages[$messageId]);
    }

    public function render() {
        return view('themes.' . config('app.settings.theme') . '.components.app');
    }
}
