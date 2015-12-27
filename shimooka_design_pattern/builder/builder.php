<?php
/*
 * 目的
 * 1.オブジェクトの生成過程と生成手順を分離する。
 *   生成手順をBuilder Classに隠す。
 */

/*
 * MEMO
 * Builder Patternは、Strategy Patternと似ていると思った。 
 * Strategy Patternは、複雑なロジックを1つのクラスに隠す。
 * Builder Patternは、オブジェクト生成の実装部分を隠す。
 */

/*
 * 参考
 * PHPによるデザインパターン
 * http://d.hatena.ne.jp/shimooka/20141216/1418706609
*/


/*
 * Product Class
 */
class News
{
    private $title;
    private $url;
    private $target_date;

    public function __construct($title, $url, $target_date)
    {
        $this->title = $title;
        $this->url = $url;
        $this->target_date = $target_date;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getDate()
    {
        return $this->target_date;
    }
}


/*
 * Director Class
 */
class NewsDirector
{
    //builderクラスに委譲
    private $builder;
    private $url;

    public function __construct(NewsBuilder $builder, $url)
    {
        $this->builder = $builder;
        $this->url = $url;
    }

    public function getNews()
    {
        $news_list = $this->builder->parse($this->url);
        return $news_list;
    }
}


/*
 * Builder Class
 */
interface NewsBuilder {
    public function parse($data);
}


/*
 * Concrete Builder Class
 */
class RssNewsBuilder implements NewsBuilder
{
    public function parse($url)
    {
        //TODO RSSの仕様とRSS系のライブラリは要調査
        $data = simplexml_load_file($url);
        if ($data === false) {
            throw new Exception('read data [' .
                                htmlspecialchars($url, ENT_QUOTES, mb_internal_encoding())
                                . '] failed !');
        }
        //var_dump($data);
        $list = array();
        foreach ($data->item as $item) {
            $dc = $item->children('http://purl.org/dc/elements/1.1/');
            $list[] = new News($item->title,
                               $item->link,
                               $dc->date);
        }
        return $list;
    }
}


/*
 * Client Class
 */
$builder = new RssNewsBuilder();
$url = 'http://www.php.net/news.rss';

$director = new NewsDirector($builder, $url);
foreach ($director->getNews() as $article) {
    printf('<li>[%s] <a href="%s">%s</a></li>',
           $article->getDate(),
           $article->getUrl(),
           htmlspecialchars($article->getTitle(), ENT_QUOTES, mb_internal_encoding())
    );
}
