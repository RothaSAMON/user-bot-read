<?php

namespace App\Telegram\Madeline;

use danog\MadelineProto\EventHandler;
use danog\MadelineProto\API;

class Handler extends EventHandler
{
  public function onUpdateNewMessage(array $update)
  {
    // ignore outgoing messages
    if (!empty($update['message']['out'])) {
      return;
    }

    $msg = $update['message'];

    // Only handle text messages (optional)
    $text = $msg['message'] ?? null;

    // Get chat/group id (peer)
    $peerId = $this->getPeerId($msg['peer_id'] ?? null);

    // Get sender id
    $fromId = $this->getFromId($msg['from_id'] ?? null);

    $allowedBotIds = $this->envIds('TG_TARGET_BOT_IDS');
    $allowedGroupIds = $this->envIds('TG_TARGET_GROUP_IDS');

    // Filter: only messages inside allowed groups
    if ($peerId === null || !in_array((string) $peerId, $allowedGroupIds, true)) {
      return;
    }

    // Filter: only messages sent by allowed bots
    // (Bots are "users" with IDs; fromId is that bot's user id)
    if ($fromId === null || !in_array((string) $fromId, $allowedBotIds, true)) {
      return;
    }

    // ✅ At this point: message is from allowed bot AND in allowed group
    // Push to Laravel job / log / DB
    // Keep it fast (don’t block the handler)
    logger('TG matched', [
      'peer_id' => $peerId,
      'from_id' => $fromId,
      'text' => $text,
    ]);

    // Example: dispatch job
    // \App\Jobs\ProcessTelegramMessage::dispatch($peerId, $fromId, $text, $msg);
  }

  private function envIds(string $key): array
  {
    $raw = (string) env($key, '');
    return array_values(array_filter(array_map('trim', explode(',', $raw))));
  }

  private function getPeerId($peer): ?int
  {
    // peer can be: ['_' => 'peerChannel', 'channel_id' => ...]
    if (!is_array($peer) || !isset($peer['_']))
      return null;

    return match ($peer['_']) {
      'peerChannel' => -1000000000000 - (int) $peer['channel_id'], // not always needed, but common mapping
      'peerChat' => -(int) $peer['chat_id'],
      'peerUser' => (int) $peer['user_id'],
      default => null,
    };
  }

  private function getFromId($from): ?int
  {
    if (!is_array($from) || !isset($from['_']))
      return null;

    return match ($from['_']) {
      'peerUser' => (int) $from['user_id'],
      'peerChannel' => (int) $from['channel_id'], // rare as sender
      'peerChat' => (int) $from['chat_id'],
      default => null,
    };
  }
}
