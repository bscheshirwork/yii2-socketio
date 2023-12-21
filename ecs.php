<?php

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/commands',
        __DIR__ . '/drivers',
        __DIR__ . '/events',
        __DIR__ . '/Broadcast.php',
        __DIR__ . '/EventManager.php',
        __DIR__ . '/Process.php',
        __DIR__ . '/SocketIoAsset.php',
    ]);

    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::PSR_12,
        SetList::SYMPLIFY,
    ]);

    $ecsConfig->ruleWithConfiguration(GeneralPhpdocAnnotationRemoveFixer::class, [
        'annotations' => ['author', 'package', 'group', 'covers', 'category'] //оставляем 'throws'
    ]);
    $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arrays', 'arguments', 'parameters', 'match'],
    ]);

    $ecsConfig->skip([
        DeclareStrictTypesFixer::class,
        AssignmentInConditionSniff::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        LineLengthFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        ParamReturnAndVarTagMalformsFixer::class,
        PhpdocVarWithoutNameFixer::class,
        MethodChainingNewlineFixer::class,
    ]);
};
