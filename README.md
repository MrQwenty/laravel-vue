# Laravel 9 starter template

## ⚒️ Installazione
Per installare il progetto, eseguire i comandi:
```shell
composer install
npm install
php artisan migrate
cp .env.example .env
```

## ⚡ Avvio
```shell
npm run dev
```
```shell
php artisan serve
```

## 🖌️ Libreria frontend (Vue)
E' pre-installata la libreria [Naive-ui](https://www.naiveui.com/) 

## ☠️ Errore comune
E' possibile riscontrare un errore riguardante la cartella `lang`, occorre 
eliminare la cartella `resources/lang` e rilanciare `npm run dev`

## 🇮🇹 Traduzioni
E' installata una libreria per la gestione delle traduzioni. Nella cartella `resources/lang` 
sono presenti le cartelle con le varie lingue disponibili alle traduzioni.

In ogni cartella sono poi presenti i file che, suddivisi per relative sezioni, presentano all'interno un array associativo, 
che vuole come chiave la stringa che verrà chiamata a front-end, e come valore il relativo valore nella lingua che stiamo modificando. 

Se la libreria non trova una relativa chiave, sarà stampato a video la stringa della chiave stessa.

E' possibile gestire file e cartelle liberamente.
