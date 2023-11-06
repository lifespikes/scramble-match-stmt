# scramble-match-stmt
This Scramble extension was made as an experiment of how usable PhpParser may be when building extensions.

In essence, it allows you to scan `JsonResource` classes whose `toArray` features a dynamic return type through a `match` statement.

## Analyzing the code
Scramble gives us the FQN of the type being analyzed, using `ReflectionClass`, we get the file path of the class.

We fetch the contents and use Scramble's `FileParser` to get the AST of the file we intend to analyze. This returns a `FileParserResult` object that is essentially a `PhpParser` instance.

Once we have a file, we use the `getMethod` method to help us get to the `toArray` method easier.

Then, we traverse through the nodes:
- First we find the `return` statement
- Then the `match`
- And then we get the first `MatchArm_`

Lastly, we get the body of the `MatchArm_` and verify it is an array.

## Casting to Scramble types
We had to go digging and used code from the `MethodAnalyzer` and `ArrayItemHandler` classes.

Then we mimicked some of their behavior and re-used other internal classes like `Scope` and `TypeHelper` to cast our PhpParser nodes to Scramble types.

Lastly, we used the `TypeTransformer` to turn everything into an usable OpenAPI Schema.

## More info?
Read the code, and some comments I sprinkled around for more details.
