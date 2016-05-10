# XHP Block Template

## Intro

This is a simple library to compile an XHP template expression into an ordinary
Hack class. We do this because we need to have a template inheritance and
inclusion in XHP.

Features:
- Template block/inclusion
- Accessing outer template from within
- Template inheritance, like `<xbt:template extends="base.xbt.php">`
- Accessing parent blocks with `<xbt:parent />`

Example: suppose that we have this `xbt` template, named `template.xbt.php`:
```
<xbt:template>
    <h1>This is a header</h1>
    <xbt:block name="main">
        <p>This is inside a block</p>
        <xbt:block name="submain">
            <p>This is a block inside a block</p>
        </xbt:block>
    </xbt:block>
    <p>This is a simple footer</p>
</xbt:template>
```

Load the file, and using this library, we can convert the template into an XHP
class
```
$contents    = file_get_contents("template.xbt.php");
$tokenizer   = new Tokenizer($contents);
$tokenStream = $tokenizer->tokenize();
$parser      = new Parser($tokenStream);
$result      = $parser->parse();
```

`$result` should be like something like this
```
<?hh

class __xbt_4bc492834a61e30d17d158c6a052837584b1db90 extends App\Publishing\Lib\Xbt\Runtime
{
    public function render()
    {
        return <x:frag>
    <h1>This is a header</h1>
    {$this->block_main()}
    <p>This is a simple footer</p>
</x:frag>;
    }

    public function block_main()
    {
        return <x:frag>
    <p>This is inside a block</p>
     {$this->block_submain()}
</x:frag>;
    }

    public function block_submain()
    {
        return <x:frag>
    <p>This is a block inside a block</p>
</x:frag>;
    }
}
```


## Laravel Binding

Include the `XbtServiceProvider` to your project. Compiled xhp classes are in
`$app['path.storage'].'/views'`, and compiled laravel views from that classes
are in `$app['path.storage'].'/xbt'`.


## Process

```

File -> Tokenizer -> TokenStream -> Parser -> Node -> Xhp

```

`Tokenizer` will tokenize the template into a `TokenStream`. We pass the
`TokenStream` into the `Parser`, and call the method `parse` to get the
`Template` node, the highest level representation node for a given template.

`Node` is the abstraction of xhp class in php/hack. The parsing process will
turn a template file into a `Node` before writing into files. Method `compile`
in `Template` will output XHP code.

