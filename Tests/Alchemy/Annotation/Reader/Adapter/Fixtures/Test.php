<?php
/**
 * @Role(Super)
 */
class Test
{
    /**
     * @Role(Administrator)
     * @Permission(allow = [perm1, perm2])
     */
    public function app()
    {
    }
}
