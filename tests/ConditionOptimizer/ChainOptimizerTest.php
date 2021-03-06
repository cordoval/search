<?php

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests\ConditionOptimizer;

use Prophecy\Prophecy\ObjectProphecy;
use Rollerworks\Component\Search\ConditionOptimizer\ChainOptimizer;
use Rollerworks\Component\Search\Test\SearchConditionOptimizerTestCase;
use Rollerworks\Component\Search\ValuesBag;
use Rollerworks\Component\Search\ValuesError;
use Rollerworks\Component\Search\ValuesGroup;

final class ChainOptimizerTest extends SearchConditionOptimizerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $optimizer1;

    /**
     * @var ObjectProphecy
     */
    private $optimizer2;

    /**
     * @var ChainOptimizer
     */
    protected $optimizer;

    protected function setUp()
    {
        parent::setUp();

        $this->optimizer = new ChainOptimizer();

        $this->optimizer1 = $this->prophet->prophesize('Rollerworks\Component\Search\SearchConditionOptimizerInterface');
        $this->optimizer2 = $this->prophet->prophesize('Rollerworks\Component\Search\SearchConditionOptimizerInterface');
        $this->optimizer1->getPriority()->willReturn(0);
        $this->optimizer2->getPriority()->willReturn(5);
    }

    /**
     * @test
     */
    public function it_allows_adding_formatters()
    {
        $this->optimizer->addOptimizer($this->optimizer1->reveal());
        $this->optimizer->addOptimizer($this->optimizer2->reveal());
    }

    /**
     * @test
     */
    public function it_execute_the_registered_formatters_priority_order()
    {
        $searchCondition = $this->prophet->prophesize('Rollerworks\Component\Search\SearchConditionInterface');
        $searchCondition->getValuesGroup()->willReturn(new ValuesGroup());

        $checkValue = [];

        $this->optimizer1->process($searchCondition)->will(function () use (&$checkValue) { $checkValue[] = 2; });
        $this->optimizer2->process($searchCondition)->will(function () use (&$checkValue) { $checkValue[] = 1; });

        $this->optimizer->addOptimizer($this->optimizer1->reveal());
        $this->optimizer->addOptimizer($this->optimizer2->reveal());

        $this->optimizer->process($searchCondition->reveal());
        $this->assertSame([1, 2], $checkValue);
    }

    /**
     * @test
     */
    public function it_should_not_execution_when_ValuesGroup_has_violations()
    {
        $valuesGroup = new ValuesGroup();
        $values = new ValuesBag();
        $values->addError(new ValuesError('ranges[0].lower', 'whoops'));
        $valuesGroup->addField('id', $values);

        $searchCondition = $this->prophet->prophesize('Rollerworks\Component\Search\SearchConditionInterface');
        $searchCondition->getValuesGroup()->willReturn($valuesGroup);

        $this->optimizer1->process($searchCondition)->shouldNotBeCalled();
        $this->optimizer->addOptimizer($this->optimizer1->reveal());

        $this->optimizer->process($searchCondition->reveal());
    }

    /**
     * @test
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Unable to add optimizer to its own chain.
     */
    public function it_errors_when_adding_self()
    {
        $this->optimizer->addOptimizer($this->optimizer);
    }
}
