<?php
use Littled\App\LittledGlobals;
use Littled\PageContent\ContentUtils;
use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\Filters\TestTableContentFiltersTestHarness;
use Littled\Utility\LittledUtility;

/** @var PageContent $page_data */
/** @var TestTableContentFiltersTestHarness $filters */

?>
<div class="listings test-listings">
    <table>
        <tr>
            <th>Name</th>
            <th>Int Field</th>
            <th>operations</th>
        </tr>
<?php
try {
    debug_print_backtrace();
    $listings_data = $filters->retrieveListings();
    $template_path = LittledUtility::joinPaths(LittledGlobals::getLocalTemplatesPath(), 'content/test_table/listings-cell.php');
    foreach($listings_data as $row) {
        ContentUtils::renderTemplateWithErrors($template_path, array('cell_content' => $row));
    }
}
catch(Exception $e) {
    ContentUtils::printError($e->getMessage());
}
?>
    </table>
</div>
