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

    $allowedGroups = $this->envArray('TG_TARGET_GROUP_IDS');
    $allowedBots = $this->envArray('TG_TARGET_BOT_IDS');
    $senderId = $message->senderId;

    // Group check
    if (!in_array((string) $message->chatId, $allowedGroups, true)) {
        return;
    }
    // Check Bot but not target
    if (!in_array((string) $senderId, $allowedBots, true)) {
        return;
    }

    logger('new msg :', [$message]);
    $this->logger("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    $this->logger("New Message Received!");
    $this->logger("  Chat ID: {$chatId}");
    $this->logger("  Sender ID: {$senderId}");
    $this->logger("  Message: {$text}");
    $this->logger("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

    // ===================
    // Test case: when our server throw error, if telegram retries or not. NOTE: telegram webhook is keep retries until status:200
    // Check startAndLoop compare with setEventHandler
    
    // Check is server down: when One User join many groups and send many messages
    // ===================
  }

  /**
   * Parse env string to array
   * @param string $key
   * @return string[]
   */
  private function envArray(string $key): array
  {
    return array_filter(
      array_map('trim', explode(',', (string) getenv($key)))
    );
  }

}