<?php

namespace App\Providers;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ValidationRulesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->addValidationRules();
    }

    protected function addValidationRules(): void
    {
        $path = app_path('Rules');
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $classDir = Str::of($file->getPathname())
                ->between($path, '.php')
                ->replace("/", "\\")
                ->prepend('\\App\\Rules');

            $ruleName = $classDir->afterLast('\\')
                ->snake();

            if ($this->isCustomValidation($classDir)) {
                $this->extendValidator($classDir, $ruleName);
            }
        }
    }

    protected function isCustomValidation(string $classDir): bool
    {
        $isCustom = false;
        if (class_exists($classDir)) {
            $interfaces = class_implements($classDir);
            $isCustom = in_array(Rule::class, $interfaces);
        }
        return $isCustom;
    }

    protected function extendValidator(string $classDir, string $ruleName): void
    {
        Validator::extend($ruleName, function ($attribute, $value, $parameters, $validator) use ($classDir, $ruleName) {
            $rule = new $classDir();
            if (method_exists($rule, 'setData')) {
                $rule->setData($parameters);
            }
            if (method_exists($rule, 'setValidator')) {
                $rule-> setValidator($validator);
            }
            $passes = $rule->passes($attribute, $value);
            $customMessage = $rule->message();
            // Replace dynamic error message with the custom rule return message
            $validator->addReplacer($ruleName, function ($message, $attribute) use ($customMessage) {
                $message = Str::replace("custom_rule_message", $customMessage, $message);
                return __($message, ['attribute' => $attribute]);
            });
            return $passes;
        }, 'custom_rule_message');
    }
}
