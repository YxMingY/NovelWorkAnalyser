<?php
function count_black(string $line):int
{
  $chars = str_split(rtrim($line));
  for ($i=0;$i<count($chars);$i++) {
    if($chars[$i] != " ") return $i;
  }
  return 0;
}
function analyse(string $text):void
{
  $lines = explode("\n",$text);
  global $chs;
  /*
   * 统计：
   *   大标题（空五格）
   *   小标题（空三格）
   *   段落（空八格）
   *   字数
   */
  $big_titles = [];
  $small_titles = [];
  $paragraphs = [];
  $without_blacks = [];
  $lengths = [];
  $letter_wrongs = [];
  $symbol_wrongs = [];
  $black_wrongs = [];
  $lines_info = [];
  for ($i=0;$i<count($lines);$i++) {
    $lengths[$i] = round(strlen(trim($lines[$i]))/3);
    if(preg_match('/[a-z]/',$lines[$i])) {
      $letter_wrongs[] = $i+1;
    }
    if(preg_match('/:\?"\'/',$lines[$i])) {
      $symbol_wrongs[] = $i + 1;
    }
    switch ($cb=count_black($lines[$i])) {
      case 0:
        if(strlen(trim($lines[$i]))!=0) {
          $without_blacks[] = $i+1;
          $lines_info[$i] = "开头无空格段落";
        }else {
          $lines_info[$i] = "空行";
        }
        break;
      case 5:
        $lines_info[$i] = "大标题";
        $big_titles[] = $i+1;
        break;
      case 3:
        $lines_info[$i] = "小标题";
        $small_titles[] = $i+1;
        break;
      case 8:
        $lines_info[$i] = "普通段落";
        $paragraphs[] = $i+1;
        break;
      default:
        $lines_info[$i] = "疑似错误行";
        $black_wrongs[] = [$i+1,$cb];
    }
  }
  /*
   * 查错：
   *   1.有小写字母√
   *   2.有英文符（?:"')√
   *   3.空格数量出错√
   *   4.空行数量出错（标题之间一行，标题与段两行，段与段两行）
   */
  $inter_wrongs = [];
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
        if ($inter_line != 1) {
          $inter_wrongs[] = [$i + 1, $entity + 1, 1, $inter_line];
        }
      }else {
        if ($inter_line != 2) {
          $inter_wrongs[] = [$i + 1, $entity + 1, 2, $inter_line];
        }
      }
      $entity = $i;
    }
  }
  echo "统计(字数包括标点):"."<br />".
       "&nbsp;&nbsp;大标题: ".count($big_titles)."个"."<br />";
  foreach ($big_titles as $big_title) {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;第".$big_title."行(".$lengths[$big_title-1]."字)"."<br />";
  }
  echo "&nbsp;&nbsp;小标题: ".count($small_titles)."个"."<br />";
  foreach ($small_titles as $small_title) {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;第".$small_title."行(".$lengths[$small_title-1]."字)"."<br />";
  }
  echo "&nbsp;&nbsp;段落: ".count($paragraphs)."个"."<br />";
  foreach ($paragraphs as $paragraph) {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;第".$paragraph."行(".$lengths[$paragraph-1]."字)"."<br />";
  }
  echo "&nbsp;&nbsp;开头无空格段落: ".count($without_blacks)."个"."<br />";
  foreach ($without_blacks as $without_black) {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;第".$without_black."行(".$lengths[$without_black-1]."字)"."<br />";
  }
  echo "共".array_sum($lengths)."字"."<br />";
  echo "查错:"."<br />";
  if(!empty($letter_wrongs)) {
    echo "&nbsp;&nbsp;以下行出现了小写字母，英文字母要一律大写哦！"."<br />";
    foreach ($letter_wrongs as $letter_wrong) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;第" . $letter_wrong . "行 (" . ((strlen($l=trim($lines[$letter_wrong-1])) < 21) ? $l : substr($l, 0, 21)) . "...)"."<br />";
    }
  }
  if(!empty($symbol_wrongs)) {
    echo "&nbsp;&nbsp;以下行出现了英文符号，是不是手误？检查一下！"."<br />";
    foreach ($symbol_wrongs as $symbol_wrong) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;第" . $symbol_wrong . "行 (" . ((strlen($l=trim($lines[$symbol_wrong-1])) < 21) ? $l : substr($l, 0, 21)) . "...)"."<br />";
    }
  }
  if(!empty($black_wrongs)) {
    echo "&nbsp;&nbsp;以下行的开头空格数有问题，检查一下！"."<br />";
    foreach ($black_wrongs as $black_wrong) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;第" . $black_wrong[0] . "行的开头居然空了".$black_wrong[1]." 格？？ (" . (strlen($l = trim($lines[$black_wrong[0]-1])) < 21 ? $l : substr($l, 0, 21)) . "...)"."<br />";
    }
  }
  if(!empty($inter_wrongs)) {
    echo "&nbsp;&nbsp;以下行间的空行数有问题，检查一下！(开头空格个数错误的行，视作普通段落)"."<br />";
    foreach ($inter_wrongs as $inter_wrong) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;第" . $inter_wrong[1] . "行(".(strlen($l = trim($lines[$inter_wrong[0]-1])) < 21 ? $l : substr($l, 0, 21)) ."...)和第".$inter_wrong[0]."行(".(strlen($l = trim($lines[$inter_wrong[1]-1])) < 21 ? $l : substr($l, 0, 21)) ."...)间应该空".$inter_wrong[2]."行,你空了".$inter_wrong[3]."行。"."<br />";
    }
  }
  if(empty($letter_wrongs)&&empty($symbol_wrongs)&&empty($black_wrongs)&&empty($inter_wrongs)) {
    echo "&nbsp;&nbsp;emm,我没发现你的格式错误，你好棒哦！"."<br />";
  }
  //print_r($lines_info);
}
