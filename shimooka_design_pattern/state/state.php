<?php
/*
 * 目的
 * 1.状態をコードで再現する
 * 状態に関する処理を、StateClass群に閉じ込める
 * →条件文などを使わないのでコードが分かりやすくなるし、後から状態を増やすのも厳しくない。
 * 2.Contextを修正する機会が減る。
 * (振る舞いの大枠をStateClass,それぞれの振る舞いをConcreteStateClassに切り出すから。)
*/


/**
 * Context Class
 * State Classを移譲で利用。状況によって振る舞いが変わるから、こういう名前なのだろう。
 */
class User
{
    private $name;
    private $state;	//Stateを移譲で保持する
    private $count = 0;

    public function __construct($name)
    {
        $this->name = $name;
        $this->state = UnauthorizedState::getInstance();
        $this->resetCount();
    }

    public function switchState()
    {
        echo "状態遷移:" . get_class($this->state) . "→";
        $this->state = $this->state->nextState();
        echo get_class($this->state) . "<br>";
        $this->resetCount();
    }

    public function isAuthenticated()
    {
    	//具体的な状態の遷移は、State側にぶん投げる。
        return $this->state->isAuthenticated();
    }

    public function getMenu()
    {
        return $this->state->getMenu();
    }

    public function getUserName()
    {
        return $this->name;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function incrementCount()
    {
        $this->count++;
    }

    public function resetCount()
    {
        $this->count = 0;
    }
}


/**
 * Stateクラスに相当する
* 状態毎の動作・振る舞いを定義する
*/
interface UserState {
	public function isAuthenticated();
	public function nextState();
	public function getMenu();
}


/**
 * ConcreteStateクラスに相当する
 * 認証後の状態を表すクラス
 */
class AuthorizedState implements UserState
{

	private static $singleton = null;

	private function __construct()
	{
	}

	public static function getInstance() {
		if (self::$singleton == null) {
			self::$singleton = new AuthorizedState();
		}
		return self::$singleton;
	}

	public function isAuthenticated()
	{
		return true;
	}

	public function nextState()
	{
		//状態を知っているのはStateClass達。
		//これで、ContextとStateが疎結合になる。
		return UnauthorizedState::getInstance();
	}

	public function getMenu()
	{
		$menu = '<a href="?mode=inc">カウントアップ</a> | '
				.    '<a href="?mode=reset">リセット</a> | '
						.    '<a href="?mode=state">ログアウト</a>';
						return $menu;
	}

	public final function __clone() {
		throw new RuntimeException ('Clone is not allowed against ' . get_class($this));
	}
}


/**
 * ConcreteStateクラスに相当する
 * 未認証の状態を表すクラス
 */
class UnauthorizedState implements UserState
{
	private static $singleton = null;

	private function __construct()
	{
	}

	public static function getInstance() {
		if (self::$singleton === null) {
			self::$singleton = new UnauthorizedState();
		}
		return self::$singleton;
	}

	public function isAuthenticated()
	{
		return false;
	}

	public function nextState()
	{
		return AuthorizedState::getInstance();
	}

	public function getMenu()
	{
		$menu = '<a href="?mode=state">ログイン</a>';
		return $menu;
	}

	public final function __clone() {
		throw new RuntimeException ('Clone is not allowed against ' . get_class($this));
	}
}


/**
 * Client
 */
session_start();

$context = isset($_SESSION['context']) ? $_SESSION['context'] : null;
if (is_null($context)) {
	$context = new User('ほげ');
}

$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');
switch ($mode) {
	case 'state':
		echo '<p style="color: #aa0000">状態を遷移します</p>';
		$context->switchState();
		break;
	case 'inc':
		echo '<p style="color: #008800">カウントアップします</p>';
		$context->incrementCount();
		break;
	case 'reset':
		echo '<p style="color: #008800">カウントをリセットします</p>';
		$context->resetCount();
		break;
}

$_SESSION['context'] = $context;

echo 'ようこそ、' . $context->getUserName() . 'さん<br>';
echo '現在、ログインして' . ($context->isAuthenticated() ? 'います' : 'いません') . '<br>';
echo '現在のカウント：' . $context->getCount() . '<br>';
echo $context->getMenu() . '<br>';

