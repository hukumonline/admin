<?php
class MasterStatus
{
    static function getPublishingStatus()
    {
        $a = array();
        $a[-1] = 'archived';
        $a[0] = 'draft';
        $a[1] = 'approved';
        $a[2] = 'NA';
        $a[9] = 'selected';
        $a[99] = 'published';

        return $a;
    }
    static function ignoreUserMigration()
    {
        $a = array();
        $a[0] = 'zapatista';
        $a[1] = 'nihki';
        $a[2] = 'msutan';
        $a[3] = 'dedi';
        $a[4] = 'galuh2009';
        $a[5] = 'mismail.kri';
        $a[6] = 'tatang';
        $a[7] = 'yuni2009';
        $a[8] = 'acielino';
        $a[9] = 'Rahmat malik';
        $a[10] = 'parhati';
        $a[11] = 'muhammadiqsansirie';
        $a[12] = 'tatabawel';
        $a[13] = 'dedi2008';
        $a[14] = 'dedykristianto';
        $a[15] = 'laugordo';
        $a[16] = 'online01';

        return $a;
    }
}