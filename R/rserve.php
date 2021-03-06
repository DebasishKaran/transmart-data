<?php
if (!isset($_ENV['TRANSMART_USER'])) {
        fprintf(STDERR, "TRANSMART_USER is not set\n");
        exit(1);
}
$u = $_ENV['TRANSMART_USER'];
$r = __DIR__ . '/root/bin/R';
?>
#!/bin/bash

### BEGIN INIT INFO
# Provides:             rserve
# Required-Start:       $local_fs $remote_fs $network
# Required-Stop:        $local_fs $remote_fs $network
# Default-Start:        2 3 4 5
# Default-Stop:         0 1 6
# Short-Description:    rserve
### END INIT INFO

function do_start {
        # Rserve does not daemonize properly. We need to reopen stdout
        # as /dev/null, otherwise we get all kinds of output
        # The standard way to do it is to connect /dev/null to file
        # descriptors 0, 1 and 2 only in the child (daemonized) process
        # but obviously we are unable to do that
        exec 7>&1
        exec 1>/dev/null
        su - -c "<?= $r ?> CMD Rserve --quiet --vanilla" <?= $u, "\n" ?> >&7
        EXIT_VAL=$?
        exec 1>&7 7>&-
        if [ $EXIT_VAL -eq 0 ]; then
                echo "Rserve started"
        else
                echo "Failed starting Rserve"
        fi
}

function do_stop {
        if pgrep -u <?= $u ?> -f Rserve  > /dev/null
        then
                kill `pgrep -u  <?= $u ?> -f Rserve`
				if [ $? -eq 0 ]; then
						echo "Rserve killed"
				else
						echo "Failed killing Rserve"
				fi
        else
                echo "nothing to stop; Rserve is not running"
                exit 0
        fi
}

case "$1" in
        start)
                do_start
        ;;

        stop)
                do_stop
        ;;

        restart|reload|force-reload)
                do_stop
                do_start
        ;;

        status)
                if pgrep -u <?= $u ?> -f Rserve > /dev/null
                then
                        echo "Rserve service running."
                        exit 0
                else
                        echo "Rserve is not running"
                        exit 1
                fi
        ;;

        *)
                echo "Usage: $0 {start|stop|status|restart|force-reload|reload}" >&2
                exit 1
        ;;
esac
