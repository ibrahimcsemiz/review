<?php

function generateRandomColor(){
    $color = '#';
    $colorHexLighter = array("9","A","B","C","D","E","F" );
    for ($x = 0; $x < 6; $x++) {
        $color .= $colorHexLighter[array_rand($colorHexLighter, 1)]  ;
    }
    return substr($color, 0, 7);
}

$metin = "Lorem Ipsum, dizgi ve baskı endüstrisinde kullanılan mıgır metinlerdir. Lorem Ipsum, adı bilinmeyen bir matbaacının bir hurufat numune kitabı oluşturmak üzere bir yazı galerisini alarak karıştırdığı 1500'lerden beri endüstri standardı sahte metinler olarak kullanılmıştır. Beşyüz yıl boyunca varlığını sürdürmekle kalmamış, aynı zamanda pek değişmeden elektronik dizgiye de sıçramıştır. 1960'larda Lorem Ipsum pasajları da içeren Letraset yapraklarının yayınlanması ile ve yakın zamanda Aldus PageMaker gibi Lorem Ipsum sürümleri içeren masaüstü yayıncılık yazılımları ile popüler olmuştur.";

$bolum = explode(' ', $metin);
$duzen = array();
for($i = 0; $i <= count($bolum) - 1; $i++){
	$renkli = generateRandomColor();
	$kelime = $bolum[$i];
	$renkle = '<font style="color:' . $renkli . '">' . $kelime . '</font>';
	$duzen[] = $renkle;
}
$topla = implode(' ', $duzen);
echo $topla;
