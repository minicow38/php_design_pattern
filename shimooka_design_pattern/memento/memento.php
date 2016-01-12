<?php
/*
 * 目的
 * 1.木構造を再現する
 * 2.単一のオブジェクトとその集合を同一視する。
 * ファイルとフォルダが典型的な例。
 */

/*
 * MEMO
 * Mementoとは記憶や思い出といった意味で、あるオブジェクトを保存しておくニュアンスをもたせている。
 * MementPatternは、SnapshotPatternとも呼ばれる。
 * 
 * アプリケーションでよくある、「元に戻す」「やり直す」を実現するパターン。
 */
 

/**
 * MementoClass
 */
class DataSnapshot
{
	private $comment;

	protected function __construct($comment)
	{
		$this->comment = $comment;
	}

	protected function getComment()
	{
		return $this->comment;
	}
}


/**
 * OriginatorClass
 * Originatorとは創始者という意味。
 * このクラスからスナップショットが作られるので、Originatorという名称なのだろう。
 */
final class Data extends DataSnapshot
{
	private $comment;

	public function __construct()
	{
		$this->comment = array();
	}

	public function takeSnapshot()
	{
		return new DataSnapshot($this->comment);
	}

	public function restoreSnapshot(DataSnapshot $snapshot)
	{
		$this->comment = $snapshot->getComment();
	}

	public function addComment($comment)
	{
		$this->comment[] = $comment;
	}

	public function getComment()
	{
		return $this->comment;
	}
}


/**
 * CaretakerClass
 * Caretakerとは管理者という意味。
 */
class DataCaretaker
{

	public function __construct()
	{
		if (!isset($_SESSION)) {
			session_start();
		}
	}

	public function setSnapshot($snapshot)
	{
		$this->snapshot = $snapshot;
		$_SESSION['snapshot'] = $this->snapshot;
	}

	public function getSnapshot()
	{
		return (isset($_SESSION['snapshot']) ? $_SESSION['snapshot'] : null);
	}
}


/**
 * Client
 */
session_start();

$caretaker = new DataCaretaker();
$data = isset($_SESSION['data']) ? $_SESSION['data'] : new Data();

$mode = (isset($_POST['mode'])? $_POST['mode'] : '');

switch ($mode) {
	case 'add':
		$data->addComment((isset($_POST['comment']) ? $_POST['comment'] : ''));
		break;
	case 'save':
		$caretaker->setSnapshot($data->takeSnapshot());
		echo '<font style="color: #dd0000;">データを保存しました。</font><br>';
		break;
	case 'restore':
		$data->restoreSnapshot($caretaker->getSnapshot());
		echo '<font style="color: #00aa00;">データを復元しました。</font><br>';
		break;
	case 'clear':
		$data = new Data();
}

//登録したコメントを表示する
echo '今までのコメント';
if (!is_null($data)) {
	echo '<ol>';
	foreach ($data->getComment() as $comment) {
		echo '<li>'
				. htmlspecialchars($comment, ENT_QUOTES, mb_internal_encoding())
				. '</li>';
	}
	echo '</ol>';
}

//次のアクセスで使うデータをセッションに保存
$_SESSION['data'] = $data;
?>
<form action="" method="post">
コメント：<input type="text" name="comment"><br>
<input type="submit" name="mode" value="add">
<input type="submit" name="mode" value="save">
<input type="submit" name="mode" value="restore">
<input type="submit" name="mode" value="clear">
</form>