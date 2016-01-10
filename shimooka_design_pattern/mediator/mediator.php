<?php
/*
 * 目的
 * 1.Mediatorでクラス間のやり取りを中央集権で管理する。
 * →これにより、Collegue同士が増えるにつれて、クラス間の結びつきが乗数的に増加する事態を防ぐ。
 */

/*
 * MEMO
 * 今回の例は、Collegueが複数存在する例になっていない。
 * 複数のCollegueがあるパターンだと、Mediatorのありがたみが分かるはず。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141217/1418788236
*/


/*
 * Collegue Class
 * Concrete Collegue Class
 */
 class User
{
    private $chatroom;//各Collegueは、Mediatorを委譲で保持する
    private $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setChatroom(Chatroom $value)
    {
         $this->chatroom = $value;
    }
    public function getChatroom()
    {
        return $this->chatroom;
    }
    public function sendMessage($to, $message)
    {
        $this->chatroom->sendMessage($this->name, $to, $message);
    }
    public function receiveMessage($from, $message)
    {
        printf('<font color="008800">%sさんから%sさんへ</font>： %s<hr>', $from, $this->getName(), $message);
    }
}


/*
 * Mediator Class
 * Concrete Mediator Class
 */
class Chatroom
{
    private $users = array();//Mediatorは、Collegueの集合を持つことが多いかも
    public function login(User $user)
    {
        $user->setChatroom($this);
        if (!array_key_exists($user->getName(), $this->users)) {
            $this->users[$user->getName()] = $user;
            printf('<font color="#0000dd">%sさんが入室しました</font><hr>', $user->getName());
        }
    }
    public function sendMessage($from, $to, $message)
    {
        if (array_key_exists($to, $this->users)) {
            //Collegueのメソッドは、Mediator側で呼び出す。
            $this->users[$to]->receiveMessage($from, $message);
        } else {
            printf('<font color="#dd0000">%sさんは入室していないようです</font><hr>', $to);
        }
    }
}


/*
 * Client 
 */
$chatroom = new Chatroom();

$sasaki = new User('佐々木');
$suzuki = new User('鈴木');
$yoshida = new User('吉田');
$kawamura = new User('川村');
$tajima = new User('田島');

$chatroom->login($sasaki);
$chatroom->login($suzuki);
$chatroom->login($yoshida);
$chatroom->login($kawamura);
$chatroom->login($tajima);

$sasaki->sendMessage('鈴木', '来週の予定は？') ;
$suzuki->sendMessage('川村', '秘密です') ;
$yoshida->sendMessage('萩原', '元気ですか？') ;
$tajima->sendMessage('佐々木', 'お邪魔してます') ;
$kawamura->sendMessage('吉田', '私事で恐縮ですが…') ;
