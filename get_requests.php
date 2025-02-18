<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}

$type = $_GET['type'] ?? 'pending';

// Get requests based on type
$sql = "SELECT r.*, u.name, u.designation, u.email 
        FROM requests r 
        JOIN users u ON r.user_id = u.id";

if ($type === 'pending') {
    $sql .= " WHERE r.status = 'unread'";
} elseif ($type === 'approved') {
    $sql .= " WHERE r.status = 'approved'";
} elseif ($type === 'rejected') {
    $sql .= " WHERE r.status = 'rejected'";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($requests) === 0) {
    echo "<p>No requests found.</p>";
    exit();
}

?>
<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Request Type</th>
            <th>Dates</th>
            <?php if ($type === 'all'): ?>
                <th>Status</th>
            <?php endif; ?>
            <th>Reason</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $request): ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($request['name']); ?><br>
                <small><?php echo htmlspecialchars($request['designation']); ?></small>
            </td>
            <td>
                <?php echo htmlspecialchars($request['request_type']); ?>
                <?php if ($request['leave_type']): ?>
                    <br><small>(<?php echo htmlspecialchars($request['leave_type']); ?>)</small>
                <?php endif; ?>
            </td>
            <td>
                From: <?php echo date('d/m/Y', strtotime($request['from_date'])); ?><br>
                To: <?php echo date('d/m/Y', strtotime($request['to_date'])); ?>
            </td>
            <?php if ($type === 'all'): ?>
                <td>
                    <span class="status-badge status-<?php echo $request['status'] === 'unread' ? 'pending' : $request['status']; ?>">
                        <?php echo ucfirst($request['status'] === 'unread' ? 'pending' : $request['status']); ?>
                    </span>
                </td>
            <?php endif; ?>
            <td><?php echo htmlspecialchars($request['reason']); ?></td>
            <td>
                <?php if ($type === 'pending'): ?>
                    <button onclick="handleAction(<?php echo $request['id']; ?>, 'approved')" class="btn btn-approve">Approve</button>
                    <button onclick="handleAction(<?php echo $request['id']; ?>, 'rejected')" class="btn btn-reject">Reject</button>
                <?php endif; ?>
                <button onclick="handleAction(<?php echo $request['id']; ?>, 'delete')" class="btn btn-delete">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>