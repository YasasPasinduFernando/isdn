<?php
class StockTransferLog
{
	private $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Return status logs for a given transfer_id ordered by changed_date ASC
	 * Each row contains: log_id, previous_status, new_status, changed_by, change_by_role, change_by_name, changed_date
	 */
	public function getLogsByTransferId(int $transferId): array
	{
		$sql = "SELECT log_id, transfer_id, previous_status, new_status, changed_by, change_by_role, change_by_name, changed_date
				FROM transfer_status_logs
				WHERE transfer_id = ?
				ORDER BY changed_date ASC";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$transferId]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Normalize types
		return array_map(function ($r) {
			return [
				'log_id' => (int)$r['log_id'],
				'transfer_id' => (int)$r['transfer_id'],
				'previous_status' => $r['previous_status'],
				'new_status' => $r['new_status'],
				'changed_by' => isset($r['changed_by']) ? (int)$r['changed_by'] : null,
				'change_by_role' => $r['change_by_role'],
				// keep both keys for compatibility, but frontend expects `changed_by_name`
				'change_by_name' => $r['change_by_name'],
				'changed_by_name' => $r['change_by_name'],
				'changed_date' => $r['changed_date']
			];
		}, $rows ?: []);
	}
}

?>
