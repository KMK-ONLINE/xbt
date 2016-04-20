<?hh // strict

namespace App\Publishing\Lib\Xbt;

class ParentNode implements Node
{
    protected string $blockName;
    protected TagAttributes $attributes;

    public function __construct(string $blockName, TagAttributes $attributes)
    {
        $this->name = ':xbt:parent';
        $this->blockName = $blockName;
        $this->attributes = $attributes;
    }

    public function getBlockName() : string
    {
        return $this->blockName;
    }

    public function render() : string
    {
        $parent = $this->getBlockName();

        return '{($_ = $__this->resolveParentBlock(\'' . $parent . '\')) ? call_user_func($_, $__this, $__params) : null}';
    }
}

