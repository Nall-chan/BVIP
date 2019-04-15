<?php

/**
 * Biete Funktionen um Thread-Safe auf Objekte zuzugrifen.
 */
trait Semaphore
{
    /**
     * Versucht eine Semaphore zu setzen und wiederholt dies bei Misserfolg bis zu 100 mal.
     * @param string $ident Ein String der den Lock bezeichnet.
     * @return boolean TRUE bei Erfolg, FALSE bei Misserfolg.
     */
    private function lock($ident)
    {
        for ($i = 0; $i < 100; $i++) {
            if (IPS_SemaphoreEnter(__CLASS__ . '.' . (string) $this->InstanceID . (string) $ident, 1)) {
                return true;
            } else {
                IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }

    /**
     * Löscht eine Semaphore.
     * @param string $ident Ein String der den Lock bezeichnet.
     */
    private function unlock($ident)
    {
        IPS_SemaphoreLeave(__CLASS__ . '.' . (string) $this->InstanceID . (string) $ident);
    }

}
