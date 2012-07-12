<?php

/**
 * @Foo some other strings
 * @Foo(some_label="something here")
 * @Bar({some: "array here", arr:[1,2,3]})
 */
class Sample
{
    /**
     * @Foo(some_label={some: "array here", arr:[1,2,3]})
     * @Bar(test_var = [one, two, three])
     */
    public function test()
    {
    }

    /**
     * @Role(Administrator)
     * @Permission(allow = [perm1, perm2])
     */
    public function app()
    {
    }
}
