<?php
/** Conversations between a customer and a business owner. One thread per inquiry. */
class MessageController {
  /** Max 30 messages per 10 minutes per user (basic spam protection). */
  static function throttle(int $uid): void {
    if ((int)q('SELECT COUNT(*) FROM inquiry_messages WHERE sender_id=? AND created_at > NOW() - INTERVAL 10 MINUTE', [$uid])->fetchColumn() >= 30)
      out(['error' => 'You are sending messages too quickly. Please wait a few minutes.'], 429);
  }
  /** Loads a thread and checks the current user is its customer or the business owner. */
  private function thread($id): array {
    $u = Auth::need();
    $t = q('SELECT i.*,b.name business,b.owner_id,c.name customer,p.name product FROM inquiries i JOIN businesses b ON b.id=i.business_id JOIN users c ON c.id=i.user_id LEFT JOIN products p ON p.id=i.product_id WHERE i.id=?', [$id])->fetch();
    $side = !$t ? null : ($t['user_id'] == $u['id'] ? 'customer' : ($t['owner_id'] == $u['id'] ? 'owner' : null));
    if (!$side) out(['error' => 'Conversation not found'], 404);
    return [$u, $t, $side];
  }
  function index() {
    $u = Auth::need(); $owner = $u['role'] === 'owner';
    out(q('SELECT i.id,i.business_id,b.name business,p.name product,c.name customer,i.last_message_at,' . ($owner ? 'i.unread_owner' : 'i.unread_customer') . ' unread,
      (SELECT message FROM inquiry_messages WHERE inquiry_id=i.id ORDER BY id DESC LIMIT 1) preview
      FROM inquiries i JOIN businesses b ON b.id=i.business_id JOIN users c ON c.id=i.user_id LEFT JOIN products p ON p.id=i.product_id
      WHERE ' . ($owner ? 'b.owner_id=?' : 'i.user_id=?') . ' ORDER BY i.last_message_at DESC LIMIT 100', [$u['id']])->fetchAll());
  }
  function unread() {
    $u = Auth::need();
    $n = $u['role'] === 'owner' ? q('SELECT COALESCE(SUM(i.unread_owner),0) FROM inquiries i JOIN businesses b ON b.id=i.business_id WHERE b.owner_id=?', [$u['id']])
                                : q('SELECT COALESCE(SUM(unread_customer),0) FROM inquiries WHERE user_id=?', [$u['id']]);
    out(['count' => (int)$n->fetchColumn()]);
  }
  function show($id) {
    [$u, $t, $side] = $this->thread($id);
    q("UPDATE inquiries SET unread_$side=0 WHERE id=?", [$id]); // opening a conversation marks it read for you
    $msgs = q('SELECT m.id,m.sender_id,m.message,m.created_at,s.name sender FROM inquiry_messages m JOIN users s ON s.id=m.sender_id WHERE m.inquiry_id=? ORDER BY m.id', [$id])->fetchAll();
    foreach ($msgs as &$m) $m['mine'] = $m['sender_id'] == $u['id'];
    out(['id' => (int)$t['id'], 'side' => $side, 'business_id' => (int)$t['business_id'], 'business' => $t['business'], 'customer' => $t['customer'], 'product' => $t['product'], 'messages' => $msgs]);
  }
  function reply($id) {
    [$u, $t, $side] = $this->thread($id); $msg = trim(body()['message'] ?? '');
    if ($msg === '') out(['error' => 'Write a message first.'], 422);
    self::throttle($u['id']); $other = $side === 'owner' ? 'customer' : 'owner'; $msg = mb_substr($msg, 0, 1000);
    q('INSERT INTO inquiry_messages(inquiry_id,sender_id,message) VALUES(?,?,?)', [$id, $u['id'], $msg]); $mid = (int)db()->lastInsertId();
    q("UPDATE inquiries SET last_message_at=NOW(), unread_$other=unread_$other+1 WHERE id=?", [$id]);
    if ((int)$t["unread_$other"] === 0) notify($side === 'owner' ? $t['user_id'] : $t['owner_id'], $side === 'owner' ? "{$t['business']} replied to your message" : "New message from {$u['name']} about {$t['business']}");
    out(['id' => $mid, 'sender_id' => (int)$u['id'], 'sender' => $u['name'], 'message' => $msg, 'mine' => true, 'created_at' => date('Y-m-d H:i:s')]);
  }
}