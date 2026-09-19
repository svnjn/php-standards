<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Fixtures\Rector;

// Like Livewire: the base class calls a hook by name, so the hook can't be private.
abstract class Component
{
    /**
     * @return list<string>
     */
    public function validate(): array
    {
        return method_exists($this, 'rules') ? $this->rules() : [];
    }
}

final class EditInvoice extends Component
{
    /**
     * @return list<string>
     */
    protected function rules(): array
    {
        return ['required'];
    }
}
