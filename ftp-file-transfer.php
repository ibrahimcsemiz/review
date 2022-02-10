<?php

/*
 * Uzak sunucudan FTP bağlantısı yardımıyla dosya transferi yapmayı sağlar.
 * Oluşabilecek aktarım problemleri ve işlem sürekliliğini sağlamak amacıyla hem uzak hemde yerel sunucuda dosya listesi oluşturularak kontrol sağlandı.
 * Dosyalar (resimler) daha sonra boyutlandırılmak üzere geçici bir tabloda tutuldu ve boyutlandırma işlemi tamamlandıktan sonra tablodan silindi.
 * Bu sayede daha küçük boyutlu bir tabloda eşleştirme yapıldı ve sorgu hızlandırıldı.
*/

function fileTransfer($server, $username, $password, $folder, $local, $camera)
{
    $started = microtime(true);

    $folderTxtNames = $folder . '/list.txt';
    $localTxtNames  = $local . '/list.txt';

    $filePath  = str_replace('../', '', $local);

    // İzin verilen dosya türleri
    $allowed = ['jpg', 'jpeg', 'png'];

    // FTP bağlantı ayarları
    $connection = ftp_connect($server);

    $login = ftp_login($connection, $username, $password);
    ftp_pasv($connection, true);
    ftp_set_option($connection, FTP_TIMEOUT_SEC, 10);

    if($login){
        $directory = ftp_mlsd($connection, $folder);

        foreach($directory as $img){
            if(in_array(getExt($img['name']), $allowed)){
                $ftpImages[] = $img['name'] . '#' . $img['size'];
            }
        }

        $readLocalFile = file_get_contents($localTxtNames);
        $localImages   = explode("\n", $readLocalFile);
        $arrayDiff     = array_diff($ftpImages, $localImages);

        if(count($arrayDiff) > 0){
            // Aldığımız dosyaları döngüye soktuk
            $ss = 0;
            foreach($arrayDiff as $img){
                if($ss < 100){
                    $splt = explode('#', $img);
                    $imageName = $splt[0];
                    $imageSize = $splt[1];
                    $localfile = $local . '/' . $imageName;
                    $remote = $folder . '/' . $imageName;

                    // Dosyayı indirip belirtilen klasöre aynı isimle kayıt ediyoruz
                    if(ftp_get($connection, $localfile, $remote, FTP_BINARY)){
                        $images[] = $imageName . '#' . $imageSize;
                        $split = explode('.', $imageName);
                        $timestamp = date('Y-m-d H:i:s', strtotime($split[0]));

                        // Veritabanı kontrolü
                        $checkDb = DB::getVar('SELECT * FROM temporary WHERE filePath = ?', [$filePath . '/' . $imageName]);
                        if($checkDb == 0){
                            DB::insert('INSERT INTO images (camera, filename, created) VALUES (?, ?, ?)', [$camera, $imageName, $timestamp]);
                            DB::insert('INSERT INTO temporary (filePath) VALUES (?)', [$filePath . '/' . $imageName]);
                        }
                    }
                    else{
                        if(file_exists($localfile)){
                            unlink($localfile);
                        }
                    }
                    $ss++;
                }
            }

            // Uzak sunucuda dosya isimlerinin yer aldığı txt dosyası oluşturuyoruz
            $h = fopen('php://temp', 'r+');
            fwrite($h, implode("\n", $ftpImages));
            rewind($h);

            ftp_fput($connection, $folderTxtNames, $h, FTP_BINARY, 0);
            fclose($h);

            if(!file_exists($localTxtNames)){
                touch($localTxtNames);
            }

            $newTxtNames = array_merge($localImages, $images);
            sort($newTxtNames);
            $l = fopen($localTxtNames, 'r+');
            fwrite($l, trim(implode("\n", $newTxtNames), "\n"));
            fclose($l);

            $ended = microtime(true);
            $time = round(($ended - $started), 2);

            return [count($arrayDiff), count($images), $time];
        }
        else{
            return -1;
        }
    }
    else{
        return false;
    }

    ftp_close($connection);
}
