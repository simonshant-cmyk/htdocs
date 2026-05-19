<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature   = 'reminders:send';
    protected $description = 'Send event reminder emails for events happening tomorrow';

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow();
        $from = $tomorrow->copy()->startOfDay();
        $to   = $tomorrow->copy()->endOfDay();

        $tickets = Ticket::with(['event.venue', 'user'])
            ->where('status', 'paid')
            ->whereHas('event', fn($q) => $q->whereBetween('start_datetime', [$from, $to]))
            ->get();

        $byUser = $tickets->groupBy('user_id');

        foreach ($byUser as $userId => $userTickets) {
            $user = User::find($userId);
            if (!$user || !$user->email) continue;

            $ticketData = $userTickets->map(fn($t) => [
                'title'          => $t->event?->title,
                'start_datetime' => $t->event?->start_datetime,
                'venue_name'     => $t->event?->venue?->name,
                'quantity'       => $t->quantity,
            ])->toArray();

            try {
                Mail::to($user->email)->send(new EventReminderMail(
                    recipientName: $user->first_name ?? 'Пользователь',
                    tickets: $ticketData,
                ));
                $this->info("Reminder sent to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info('Done. Processed ' . $byUser->count() . ' users.');
    }
}
