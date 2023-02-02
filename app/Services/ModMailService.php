<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;
use Settings;
use Notifications;

use App\Models\ModMail;
use App\Models\Mail\UserMail;
use App\Models\User\User;
use App\Services\UserService;

class ModMailService extends Service
{
    /**
     * Creates mod mail
     *
     * @param array $data
     * @return ModMail
     */
    public function createMail($data, $staff)
    {
        DB::beginTransaction();

        try {

            $user = User::find($data['user_id']);
            if(!$user) throw new \Exception('User not found');

            if(!isset($data['issue_strike']) || !$data['issue_strike']) {
                $data['issue_strike'] = false;
                $data['strike_count'] = 0;
            }
            $mail = ModMail::create([
                'staff_id' => $staff->id,
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'issue_strike' => $data['issue_strike'],
                'strike_count' => $data['strike_count'],
                'previous_strike_count' => $user->settings->strike_count,
                'seen' => false,
            ]);

            $user->settings->update([
                'strike_count' => $data['issue_strike'] ? $user->settings->strike_count + $data['strike_count'] : $user->settings->strike_count
            ]);

            if(Settings::get('max_strike_count') && $user->settings->strike_count >= Settings::get('max_strike_count')) {
                // ban user
                $service = new UserService;
                $service->ban(['ban_reason' => 'Banned for exceeding the maximum strike count.'], $user, $staff);
            }

        return $this->commitReturn(true);
    } catch(\Exception $e) {
        $this->setError('error', $e->getMessage());
    }
    return $this->rollbackReturn(false);
    }

    /**
     * Creates user mail
     *
     * @param array $data
     * @return UserMail
     */
    public function createUserMail($data, $sender)
    {
        DB::beginTransaction();

        try {

            $recipient = User::find($data['recipient_id']);
            if(!$recipient) throw new \Exception('Recipient not found');

            $mail = UserMail::create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'seen' => false,
            ]);

            // send a notification
            Notifications::create('DIRECT_MESSAGE_RECEIVED', $recipient, [
                'sender_url' => $sender->url,
                'sender_name' => $sender->name,
                'subject' => $mail->subject,
                'mail_id' => $mail->id
            ]);

        return $this->commitReturn(true);
    } catch(\Exception $e) {
        $this->setError('error', $e->getMessage());
    }
    return $this->rollbackReturn(false);
    }
}
