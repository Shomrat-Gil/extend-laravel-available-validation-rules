
# Laravel 8 - Extend Available Validation Rules
## _Add custom validation rules into Laravel available rules_

 
### You can learn more about Laravel Custom Validation Rules [here](https://laravel.com/docs/8.x/validation#custom-validation-rules)

  
**This method will add your custom validation rules into laravel default rules.**
Custom rule names will be available to use as `snake_cas` string (`class  FooBar  implements  Rule{}` will be available as rule name `foo_bar` 

## First: register the Laravel Service Provider:

All service providers are registered in the `config/app.php` configuration file.
To register your provider, add it to the array:

    'providers'  => [    
	    // Other Service Providers    
	    App\Providers\ValidationRulesServiceProvider::class,    
    ],

**Registering custom validation rules example:**

        <?php 
	    namespace App\Rules; 
	    use Illuminate\Contracts\Validation\Rule;
	    class FooBar implements Rule, DataAwareRule
	    {
	    /**
         * Determine if the validation rule passes.
         *
         * @param  string  $attribute
         * @param  mixed  $value
         * @return bool
         */
        public function passes($attribute, $value)
        {
            return strtoupper($value) === $value;
        }
     
        /**
         * Get the validation error message.
         *
         * @return string
         */
        public function message()
        {
            return 'The :attribute must be uppercase.';
        }
    }

Calling the Rule:

    use Illuminate\Support\Facades\Validator;     
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|foo_bar',
    ]);

**Custom rule with additional data**

        <?php 
	    namespace App\Rules; 
	    use Illuminate\Contracts\Validation\Rule;
	    class FooBar implements Rule, DataAwareRule
	    {
	    /**
	     * All of the data under validation.
	     *
	     * @var array
	     */
	    protected $data = [];	 
	    /**
	     * Set the data under validation.
	     *
	     * @param  array  $data
	     * @return $this
	     */
	    public function setData($data)
	    {
	        $this->data = $data;
	 
	        return $this;
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
            return in_array($value, $this->data);
        }
     
        /**
         * Get the validation error message.
         *
         * @return string
         */
        public function message()
        {
            return 'The :attribute must be ' . implode(" or ", $this->data);
        }
    }

Calling the Rule:

    use Illuminate\Support\Facades\Validator;     
    $validator = Validator::make($request->all(), [
        'action' => 'required|string|foo_bar:1,2',
    ]);
