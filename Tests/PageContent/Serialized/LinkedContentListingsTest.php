<?php

namespace LittledTests\PageContent\Serialized;


use Exception;
use Littled\App\LittledGlobals;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotInitializedException;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;
use PHPUnit\Framework\TestCase;
use TypeError;

class LinkedContentListingsTest extends TestCase
{
    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        LittledGlobals::setVerboseErrors(true);
        $o = new LinkedContentTestHarness();
        $o->setUpTestData();
    }

    /**
     * @throws Exception
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $o = new LinkedContentTestHarness();
        $o->tearDownTestData();
    }

    /**
     * @throws InvalidValueException
     * @throws NotInitializedException
     * @throws InvalidQueryException
     */
    public function testFetchListingsWithArguments()
    {
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->fetchLinkedListings();
        $listings_data = $o->listingsData();
        $full_count = count($listings_data);

        $o->fetchLinkedListings('s', LinkedContentTestHarness::MATCHING_NAME_FILTER);
        $listings_data = $o->listingsData();
        self::assertGreaterThan(0, count($listings_data));
        self::assertLessThan($full_count, count($listings_data));
    }

    /**
     * @throws InvalidValueException
     * @throws NotInitializedException
     * @throws InvalidQueryException
     */
    public function testFetchListingsWithoutArguments()
    {
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->fetchLinkedListings();
        $listings_data = $o->listingsData();
        self::assertGreaterThan(0, count($listings_data));
    }

    /**
     * @throws NotInitializedException
     */
    public function testGenerateListingsPreparedStmtWithArguments()
    {
        $test_term = 'foo';
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $ps = $o->generateListingsPreparedStmt('s', $test_term);
        self::assertCount(3, $ps);
        self::assertMatchesRegularExpression('/^CALL linkedParent2ListingsSelect/', $ps[0]);
        self::assertEquals('is', $ps[1]);
        self::assertCount(2, $ps[2]);
        self::assertContains(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1'], $ps[2]);
        self::assertContains($test_term, $ps[2]);
    }

    /**
     * @throws NotInitializedException
     */
    public function testGenerateListingsPreparedStmtWithExtraArguments()
    {
        $test_term = 'foo';
        $test_value = 95;
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $ps = $o->generateListingsPreparedStmt('si', $test_term, $test_value);
        self::assertCount(3, $ps);
        self::assertEquals('isi', $ps[1]);
        self::assertCount(3, $ps[2]);
        self::assertContains(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1'], $ps[2]);
        self::assertContains($test_value, $ps[2]);
    }

    /**
     * @throws NotInitializedException
     */
    public function testGenerateListingsPreparedStmtWithoutArguments()
    {
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $ps = $o->generateListingsPreparedStmt();
        self::assertCount(3, $ps);
        self::assertMatchesRegularExpression('/^CALL linkedParent2ListingsSelect/', $ps[0]);
        self::assertEquals('is', $ps[1]);
        self::assertContains(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1'], $ps[2]);
    }

    /**
     * @throws NotInitializedException
     */
    public function testListingsDataWithoutData()
    {
        $o = (new LinkedContentTestHarness())->setPrimaryId(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        self::expectException(NotInitializedException::class);
        $o->listingsData();
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\LinkedContentListingsTestDataProvider::mergeArgListsTestProvider()
     * @param array $expected
     * @param array|null $base
     * @param ...$args
     * @return void
     */
    public function testMergeArgLists(array $expected, ?array $base, ...$args)
    {
        self::assertEqualsCanonicalizing($expected, LinkedContentTestHarness::mergeArgLists_public($base, $args));
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\LinkedContentListingsTestDataProvider::mergeArgTypeStringTestProvider()
     * @param string $expected
     * @param string|null $base
     * @param string|null $arg_types
     * @return void
     */
    public function testMergeArgTypeStrings(string $expected, ?string $base, ?string $arg_types)
    {
        if ($base===null) {
            self::expectException(TypeError::class);
        }
        self::assertEquals($expected, LinkedContentTestHarness::mergeArgTypeStrings_public($base, $arg_types));
    }
}