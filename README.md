# php_design_pattern

### フォルダ構成
* shimooka_design_pattern<br> PHP界隈で有名な下岡さんのサンプルコードです(コピペ
  )。<br>私個人のリファレンスとして使っているコードなので、下岡さんご本人のコードをご参照ください。<br>phplib以下には、各コードで使うユーティリティ関数が入ってます。

### デザインパターンとオブジェクト指向に関係する用語
* 開放/閉鎖原則(Open/ClosedPrinciple,OCP)<br>あるクラスが以下のような性質を持つこと。<br>拡張に対して解放されている(容易に拡張が行える)
<br>修正に対して閉鎖せれている(新しい機能を追加しても、もう一度既存箇所をテストする必要がない)<br>
https://ja.wikipedia.org/wiki/%E9%96%8B%E6%94%BE/%E9%96%89%E9%8E%96%E5%8E%9F%E5%89%87

* 単一責任の原則(Single Responsibility Principle,SRP)<br>あるクラスは、たった一つの事象についてしか責任を負ってはいけないし、<br>その責任が当該クラスによってカプセル化されていること。<br>例えば、一つのクラスがデータのシリアライズ/デシリアライズと表示の両方を行っているような場合、SRPは守れていない。<br>Model-View-Controllerアーキテクチャ等に従い、複数のクラスに分けること。<br>https://en.wikipedia.org/wiki/Single_responsibility_principle




### 参考文献
* PHPによるデザインパターン入門<br>Do You PHP はてな 下岡 秀幸様<br> http://d.hatena.ne.jp/shimooka/20141211/1418298136

* 主に言語とシステム開発に関して<br>デザインパターンを考え方や重要度で分類している<br>http://language-and-engineering.hatenablog.jp/entry/20120330/p1

* デザインパターンを読み解く<br>これもデザインパターンの分類<br>http://www.happiese.com/system/dpattern.html

* GOF デザインパターン一覧<br>中級者向け。C++とJavaのサンプルコード <br>http://www14.atpages.jp/hirotech/top/Design_Patterns/
