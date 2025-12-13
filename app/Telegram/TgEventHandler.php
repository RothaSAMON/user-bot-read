<?php

declare(strict_types=1);

namespace App\Telegram;

use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;

class TgEventHandler extends SimpleEventHandler
{
  /**
   * Get peer(s) where to report errors.
   */
  public function getReportPeers()
  {
    return [];
  }

  /**
   * Returns a list of names for properties that will be automatically saved to the session database.
   */
  public function __sleep(): array
  {
    return ['lastMessageIds'];
  }

  /**
   * Cache of last message IDs.
   */
  private array $lastMessageIds = [];

  /**
   * Called on startup.
   */
  public function onStart(): void
  {
    $this->logger("Bot started!");
    $self = $this->getSelf();
    $this->logger("Logged in as: " . ($self['username'] ?? $self['first_name'] ?? 'Unknown'));
  }

  /**
   * Handle incoming messages.
   * 
   * Using intersection type: Incoming & Message
   * - Incoming: only incoming messages (not outgoing)
   * - Message: text messages
   */
  #[Handler]
  public function handleIncomingMessage(Incoming&Message $message): void
  {
    $text = $message->message;
    $chatId = $message->chatId;
    $senderId = $message->senderId;
    logger('new msg :', [$message]);
    $this->logger("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    $this->logger("New Message Received!");
    $this->logger("  Chat ID: {$chatId}");
    $this->logger("  Sender ID: {$senderId}");
    $this->logger("  Message: {$text}");
    $this->logger("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

    // Add your deposit transaction parsing logic here
    // Example:
    // if (preg_match('/deposit|transfer|ដាក់ប្រាក់/i', $text)) {
    //     $this->parseDepositTransaction($text, $senderId, $chatId);
    // }

    // Optional: Reply to the message
    // $message->reply("Got your message: {$text}");
  }
}