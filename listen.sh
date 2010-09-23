#!/usr/bin/env bash

read nick channel sender cmd args << EOT
  $(IFS=" "; echo $1)
EOT

cd ..

case $cmd in
  balance)
    /usr/bin/php balance.php $nick $channel $sender $cmd $args
    ;;
  skillqueue)
    /usr/bin/php skillqueue.php $nick $channel $sender $cmd $args
    ;;
  transactions)
    /usr/bin/php transactions.php $nick $channel $sender $cmd $args
    ;;
esac
