<?php
namespace App\Helpers;

class DataService {
    /**
     * defineOffset
     * Limit the provided array to offset according to pageNr and pageSize.
     * 
     * @param array $data
     * @param int $pageNr
     * @param int $pageSize
     * @return array
     * */
    public static function defineOffset($data, $pageNr, $pageSize) {
        $offset = 0;
        $pageNr = $pageNr ?? null;
        if (isset($pageNr) && is_numeric($pageNr) && $pageNr > 1) {
            $offset = ($pageNr-1) * $pageSize;
        }
        $result_output['length'] = count($data);
        $result_output['orders'] = array_slice($data, $offset, $pageSize);
        return $result_output;
    }

    /**
     * filterSearchResult
     * Use array filter on array and return only matched items
     * 
     * @param array $data
     * @param string $search
     * @return array
     * */
    public static function filterSearchResult($data, $search) {
        $searchResult = array();
        $searchTerm = $search ?? null;
        foreach ($data AS $item) {
            if (
                !isset($searchTerm) 
                || array_filter($item, function($e) use ($searchTerm) {
                    return (strpos($e, $searchTerm) !== false);
                }) 
                || empty($searchTerm)
            ) {
                $searchResult[] = $item;
            }
        }
        return $searchResult;
    }
}
?>