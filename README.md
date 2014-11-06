# PHP Batch Mailer

This is a simple PHP batch mailer, a `CSVMailer` class. It must be run from command-line.
It allows to sent e-mails with many attachments to many recipients.

This code can also be used as an example of how to send mails with attachments in
pure PHP (using `mail()` function only and proper headers), without need of any mailing
library. Of course, that it is thousand times better to use ready, out-of-the box
solution, like [PHPMailer](http://phpmailer.worxware.com/). But, sometimes you can't
use an external library and sometimes you want to prove yourself, that you can do
this without it.

It, of course, can also be an example of how to write command-line scripts in PHP,
for peoples, how doesn't tasted this kind of pleasure so far.

Note, that `CSVMailer` class it is part of bigger, private project. You'll find 
some border lines which you maybe will need to change to suit your needs.

## Installation

Copy `mailer.php` anywhere.

As with every piece of PHP, that bases on `mail()` function, you have to properly
configure your server and MTA (mail transport agent) to actually send e-mails. Just
because `mail()` function return `TRUE` or `CSVMailer` class display `OK`, doesn't
mean that e-mail was actually send or received by destination. Million of nasty
things may happen in between.

## Usage

Execute from command-line like this:

    php mailer.php list.csv
    
Where `list.csv` is a CSV file (any name supported) with e-mails in following format:

    T|N;filename;receipient
    
Each recipient must be put into separate line and each line must contain exactly
three fields, separated with `;`. First value is either `T` or `N` and points to
a subfolder (in a folder, from which `mailer.php` was executed), where files are
kept. Second field names file from that folder and last field must be a valid e-mail
address of the recipient.

You can, of course, create this file in Excel or any other program, that supports
CSV files. Only remember to keep each recipient in separate line and that each line
must contain three fields.

If this is too complicated, read next chapter for the live example.

In current version `CSVMailer` is set to send digitally signed PDFs, so it will
seek for `filename` and `filename.XAdES` files. For example `09102_T.pdf` and `09102_T.pdf.XAdES`
files. If it won't find both files, it won't sent e-mail to particular recipient
and will skip to next line of `list.csv`. Fix the code, if you don't like that.

## Some tests

File `mailer.php` is all, that you need. File `list.csv` and `T` and `N` folders
are added for tests.

And some files to `N` or `T` folders or use example ones. Remember to always add
a `file` (main file) and `file.XAdES` (signature file accompanying it). You can use 
only one of these folders, but you can't change their names without changing code,
because support for either `T` or `N` folder (and first argument in list file is
currently hard-coded.

Edit `list.csv` and change folder (1st field), filename (2nd field) and recipient
(3rd filed) for each line. And new lines (recipients) or remove unnecessary ones.
You can rename `list.csv` to anything, you want, only remember to adjust argument,
you pass to `mailer.php`.

## Last words

This code is based on few examples, pages and articles. Links to these references
are inside the code.

I don't like spam, most reasonable people doesn't like spam and the rest of intelligent
world doesn't like spam as well. If I ever learn, that you used this piece of code
for sending spam, I'll send you a swarm of _crazy bytes_, that will wipe out memory
of all the electronic devices in your entire network and ten miles around it. Yes,
we can do that. No, this is not impossible. We only convinced the world, that this
is impossible, to keep mugols, like you, silent. Remember, you have been warned.