<?php

namespace App\Mail;

use App\User;
use App\Todolist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignTaskTodolist extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $todolist;
    public $appName;
    public $isStatus;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Todolist $gaji
     * @param string $appName
     * @param bool $isTerbayar
     * @return void
     */
    public function __construct(User $user, Todolist $todolist, $appName, $isStatus)
    {
        $this->user = $user;
        $this->todolist = $todolist;
        $this->appName = $appName;
        $this->isStatus = $isStatus;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $logoPath = public_path('assets/img/LogoRSC.png');

        $mail = $this->view('account.todolist.AssignTaskMail')
            ->subject('Assign Task Baru')
            ->from('info@rumahscopusfoundation.com', $this->appName)
            ->attach($logoPath, ['mime' => 'image/png']);
    }
}
