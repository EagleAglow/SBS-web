<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

//  ===========================================================
//  |  any call to this validation is intended to fail, and 
//  |  return the error message passed to the validation
//  ===========================================================
//  example of usage:
//  $this->validate($request, [ 
//    'comment'=>new DummyFail( 'Message passed to rule class')
//  ]);
//  ===========================================================

class DummyFail implements Rule
{
    public $echo_this = 'Dummy message';
    public $dummy_msg = 'Other dummy message';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct( $DummyMsg )
    {
        $this->dummy_msg = $DummyMsg;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (0 == 1);  // nope!
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->dummy_msg;
    }
}
