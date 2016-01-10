<?php
/*
 * 目的
 * 1.ifやswitchによる条件分岐が肥大化するのを防ぐ
 * 2.チェック処理等を小さなクラスに切り出して、再利用生を高める。
 */

/*
 * MEMO
 * 「処理を依頼する側」と「実際の処理を行う側」を分離する。
 * 「実際の処理を行う側」が、HandlerClassらにあたる。
 * 
 * Chain of Respoisibility Patternは、GUIプログラミングにおける
 * イベントのバブリング※の実装にも使われている。
 * 
 * ※子クラスで発生したイベントが、泡のように親クラスにも伝播していくこと
 * 
 * 再帰ツリー構造で責任のたらい回しをしたい場合、CompositePatternと併用する。 * 
 * 今回の例は、順々にHandlerをつなげていくというものだった。
 * もう数パターン、異なる例を見てみたい。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141216/1418705161
*/


/**
 * Handler Class
 */
abstract class ValidationHandler
{
    private $next_handler;              //自分自身を委譲で保持する

    public function __construct()
    {
        $this->next_handler = null;
    }

    public function setHandler(ValidationHandler $handler)
    {
        $this->next_handler = $handler; //データ構造は、大本のHandlerで規定する
        return $this;
    }

    public function getNextHandler()
    {
        return $this->next_handler;
    }

    //チェーンの実行
    public function validate($input)    //データ構造に対する処理の仕方も、大本のHandlerで規定する
    {
        $result = $this->execValidation($input);
        if (!$result) {
            return $this->getErrorMessage();
        } elseif (!is_null($this->getNextHandler())) {  //OKだったら次のHandlerでValidate
            return $this->getNextHandler()->validate($input);
        } else {                                        //最後は、ここでOK判定
            return true;
        }
    }

    // 自クラスが担当する処理を実行
    protected abstract function execValidation($input);

    //処理失敗時のメッセージを取得する
    protected abstract function getErrorMessage();
}


/**
 * ConcreteHandler Class
 */
class AlphabetValidationHandler extends ValidationHandler
{
    protected function execValidation($input)
    {
        return preg_match('/^[a-z]*$/i', $input);
    }

    protected function getErrorMessage()
    {
        return '半角英字で入力してください';
    }
}

class NumberValidationHandler extends ValidationHandler
{
    protected function execValidation($input)
    {
        return (preg_match('/^[0-9]*$/', $input) > 0);
    }

    protected function getErrorMessage()
    {
        return '半角数字で入力してください';
    }
}

class NotNullValidationHandler extends ValidationHandler
{
    protected function execValidation($input)
    {
        return (is_string($input) && $input !== '');
    }

    protected function getErrorMessage()
    {
        return '入力されていません';
    }
}

class MaxLengthValidationHandler extends ValidationHandler
{
    private $max_length;

    public function __construct($max_length = 10)
    {
        parent::__construct();
        if (preg_match('/^[0-9]{,2}$/', $max_length)) {//0-2桁の半角数字(多分...)
            throw new RuntimeException('max length is invalid (0-99) !');
        }
        $this->max_length = (int)$max_length;
    }

    protected function execValidation($input)
    {
        return (strlen($input) <= $this->max_length);
    }

    protected function getErrorMessage()
    {
        return $this->max_length . 'バイト以内で入力してください';
    }
}


/**
 * Client
 */
if (isset($_POST['validate_type']) && isset($_POST['input'])) {
    $validate_type = $_POST['validate_type'];
    $input = $_POST['input'];

    //Handlerの組み立てはClient側で実施。ifやswitchと比べると、単純になっているのかな...?
    //チェーンの作成
    $not_null_handler = new NotNullValidationHandler();
    $length_handler = new MaxLengthValidationHandler(8);

    $option_handler = null;
    switch ($validate_type) {
        case 1:
            //include_once 'AlphabetValidationHandler.class.php';
            $option_handler = new AlphabetValidationHandler();
            break;
        case 2:
            //include_once 'NumberValidationHandler.class.php';
            $option_handler = new NumberValidationHandler();
            break;
    }

    //lengthとnullチェックは必須。オプションのチェックがあれば、それも付与する。
    if (!is_null($option_handler)) {
        $length_handler->setHandler($option_handler);
    }
    $handler = $not_null_handler->setHandler($length_handler);

    //処理実行と結果メッセージの表示
    $result = $handler->validate($_POST['input']);
    if ($result === false) {
        echo '検証できませんでした';
    } elseif (is_string($result) && $result !== '') {
        echo '<p style="color: #dd0000;">' . $result . '</p>';
    } else {
        echo '<p style="color: #008800;">OK</p>';
    }
}
?>
<form action="" method="post">
  <div>
    値：<input type="text" name="input">
  </div>
  <div>
    検証内容：<select name="validate_type">
    <option value="0">任意</option>
    <option value="1">半角英字で入力されているか</option>
    <option value="2">半角数字で入力されているか</option>
    </select>
  </div>
  <div>
    <input type="submit">
  </div>
</form>