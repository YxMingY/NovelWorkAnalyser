<?php
function count_black(string $line):int
{
  $chars = str_split(rtrim($line));
  for ($i=0;$i<count($chars);$i++) {
    if($chars[$i] != " ") return $i;
  }
  return 0;
}
function correct(string $text):string
{
  $text = str_replace(
    [",","?","!",":",": ","@"," ...","...",";","~","(",")","-"],
    ["，","？","！","：","：","@","……","……","；","～","（","）","—"],
    $text
  );
  $lines = explode("\n",$text);
  global $chs;
  /*
   * 统计：
   *   大标题（空五格）
   *   小标题（空三格）
   *   段落（空八格）
   *   字数
   */
  $lines_info = [];
  for ($i=0;$i<count($lines);$i++) {
    if(preg_match('/[a-z]/',$lines[$i])) {
      $lines[$i] = strtoupper($lines[$i]);
    }
    if(preg_match('/[:\?"\'-]/',$lines[$i])) {
      $lines[$i] = str_replace(
        [":","?","-"],
        ["：","？","—"],
        $lines[$i]
      );
    }
    switch ($cb=count_black($lines[$i])) {
      case 0:
        if(strlen(trim($lines[$i]))!=0) {
          $lines_info[$i] = "开头无空格段落";
          $lines[$i] = "        ".$lines[$i];
        }else {
          $lines_info[$i] = "空行";
        }
        break;
      case 5:
        $lines_info[$i] = "大标题";
        break;
      case 3:
        $lines_info[$i] = "小标题";
        break;
      case 8:
        $lines_info[$i] = "普通段落";
        break;
      default:
        $lines_info[$i] = "疑似错误行";
        if($cb==6) {
          $lines[$i] = "  ".$lines[$i];
        }
    }
  }
  /*
   * 查错：
   *   1.有小写字母√
   *   2.有英文符（?:"')√
   *   3.空格数量出错√
   *   4.空行数量出错（标题之间一行，标题与段两行，段与段两行）
   */
  $entity = null;
  for ($i=0;$i<count($lines_info);$i++) {
    if($lines_info[$i] != "空行" && $entity === null) {
      $entity = $i;
      continue;
    }
    if($lines_info[$i] != "空行" && $entity !== null) {
      $inter_line = $i-$entity-1;
      $a = [$lines_info[$i],$lines_info[$entity]];
      //都是标题
      if(!in_array("普通段落",$a) && !in_array("疑似错误行",$a) && !in_array("开头无空格段落",$a)) {
        if ($inter_line > 1) {
          $diff = $inter_line-1;
          for($ii=0;$ii<$diff;$ii++) {
            unset($line[$entity+$ii+1]);
          }
        } elseif($inter_line < 1) {
          $lines[$entity] .= "\n";
        }
      }else {
        if ($inter_line > 2) {
            $diff = $inter_line-2;
          for($ii=0;$ii<$diff;$ii++) {
            unset($line[$entity+$ii+1]);
          }
        } elseif($inter_line < 2) {
          $diff = 2-$inter_line;
          for($ii=0;$ii<$diff;$ii++) {
            $lines[$entity] .= "\n";
          }
        }
      }
      $entity = $i;
    }
  }
  return implode("\n",$lines);
}
require "analyse.php";
analyse($c=correct('孟盛楠正胡思乱想，盛典拍了一下她。“正说话呢，你想什么呢?孟盛楠:“没什么。
盛典觑了她一眼，“你那情郎?”孟盛楠: ..."
“也别藏着掖着的，那男的做什么的?”孟盛楠想了下，“他是干IT的。”
“这一行挺吃香，就是比较劳人。”盛典说完，又道:“他哪个公司呢，家里父母都在吧，开的什么车呀一-”
孟盛楠: ...“
“什么时候带屋来我看看。孟盛楠:“先，不急吧。”
“怎么不急--”盛典说到一半泄了气，哼了-声，“你外婆说让我别催你，得，你看着办吧。”
孟盛楠笑，“谢了您。”
她说完起身去玄关处拿包，盛典问:“干啥去?”
孟盛楠转头:“约会。“中午回来吃么?”“不了。”
说着，孟盛楠已经出了门。她走在外头才松了口气，怎么就没发现盛典现在变这样了呢。冷'));
file_put_contents("a.txt",$c);