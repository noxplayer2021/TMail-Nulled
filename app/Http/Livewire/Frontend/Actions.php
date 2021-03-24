<?php

namespace App\Http\Livewire\Frontend;

use Livewire\Component;
use App\Models\TMail;

class Actions extends Component {

    public $in_app = false;
    public $user, $domain, $domains, $email, $emails;

    public function mount() {
        $this->domains = config('app.settings.domains');
        $this->email = TMail::getEmail();
        $this->emails = TMail::getEmails();
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function create() {
        if (!$this->user) {
            return $this->showAlert('error', __('Please enter Username'));
        }
        if (!$this->domain) {
            return $this->showAlert('error', __('Please Select a Domain'));
        }
        if (in_array($this->user, config('app.settings.forbidden_ids'))) {
            return $this->showAlert('error', __('Username not allowed'));
        }
        $this->email = TMail::createCustomEmail($this->user, $this->domain);
        $this->redirect(route('app'));
    }

    public function random() {
        $this->email = TMail::generateRandomEmail();
        $this->redirect(route('app'));
    }

    public function deleteEmail() {
        TMail::removeEmail($this->email);
        $this->email = TMail::getEmail(true);
        $this->emails = TMail::getEmails();
        $this->redirect(route('app'));
    }

    public function render() {
        return view('themes.' . config('app.settings.theme') . '.components.actions');
    }

    /**
     * Private Functions
     */

    private function showAlert($type, $message) {
        $this->dispatchBrowserEvent('showAlert', ['type' => $type, 'message' => $message]);
    }
}
