<?php

namespace LifeSpikes\ScrambleMatchStmt\Traits;

use Closure;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Return_;

/**
 * This trait takes care of the grunt work. It may be computationally expensive so we
 * cache results since this is used twice in every scan.
 */
trait AnalyzesMatchStmt
{
    /**
     * We store the result here in case it needs to be used again.
     */
    protected Array_ $firstMatch;

    /**
     * We iterate over each arm until one of the arm's values matches the predicate provided in the
     * closure on our second param.
     */
    public function firstMatchByBody(Match_ $match, Closure $predicate): ?Node\MatchArm
    {
        return collect($match->arms)
            ->first(fn (Node\MatchArm $arm) => $predicate($arm->body));
    }

    /**
     * Here we attempt to find a return statement in the provided Node. If we find it, we then
     * look again for a Match statement inside the expression of the return statement.
     *
     * If it is not there, we return null. This will abort the operation.
     */
    public function findMatchStatement(Node $node): ?Match_
    {
        $returnStmt = (new NodeFinder())->findFirst($node, fn ($stmt) => $stmt instanceof Return_);

        if (!$returnStmt instanceof Return_ || !(($expr = $returnStmt->expr) instanceof Match_)) {
            return null;
        }

        return $expr;
    }

    /**
     * If we made it this far, now we just have to look for the first arm of the match statement. Once we've
     * found it, we make sure it is the type we need (should be an array), then, we cache the result
     * and return it!
     */
    protected function getFirstMatchValue(): ?Array_
    {
        if (isset($this->firstMatch)) {
            return $this->firstMatch;
        }

        $methodNode = $this->getMethod('toArray');

        if (!($matchStmt = $this->findMatchStatement($methodNode))) {
            return null;
        }

        $firstArrayArm = $this->firstMatchByBody($matchStmt, fn ($arm) => $arm instanceof Array_)?->body;

        if (!$firstArrayArm instanceof Array_) {
            return null;
        }

        return $this->firstMatch = $firstArrayArm;
    }
}
