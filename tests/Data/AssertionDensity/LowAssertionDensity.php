<?php

namespace Tests\Data\AssertionDensity;

class LowAssertionDensity
{
    /**
     * This method has low assertion density (below 2%)
     * - 1 parameter with type hint (1 assertion)
     * - Return type (1 assertion)
     *
     * Total: 2 assertions in ~120 lines = ~1.7% density
     */
    public function lowDensityMethod(array $data): array
    {
        $results = [];

        $processedData = [];
        $processedData['timestamp'] = time();
        $processedData['total_count'] = count($data);

        // A lot of code with no assertions
        $names = [];
        $values = [];
        $categories = [];
        $metrics = [
            'sum' => 0,
            'average' => 0,
            'min' => PHP_INT_MAX,
            'max' => PHP_INT_MIN,
            'count' => 0,
        ];

        // Processing loop with no assertions
        foreach ($data as $key => $item) {
            $processedItem = [];
            $processedItem['id'] = $key;
            $processedItem['original'] = $item;

            // Extract metadata
            $parts = explode('-', $key);
            $processedItem['category'] = $parts[0] ?? 'unknown';
            $processedItem['subcategory'] = $parts[1] ?? 'unknown';

            // Calculate some values
            $value = 0;
            if (isset($item['value'])) {
                $value = $item['value'];
            } else if (isset($item['amount'])) {
                $value = $item['amount'];
            } else if (isset($item['count'])) {
                $value = $item['count'] * 2;
            }

            $processedItem['calculated_value'] = $value;

            // Update metrics
            $metrics['sum'] += $value;
            $metrics['count']++;
            $metrics['min'] = min($metrics['min'], $value);
            $metrics['max'] = max($metrics['max'], $value);

            // Add to result
            $results[$key] = $processedItem;

            // Collect for grouping
            if (isset($item['name'])) {
                $names[] = $item['name'];
            }

            $values[] = $value;

            if (isset($processedItem['category'])) {
                $categories[$processedItem['category']] = ($categories[$processedItem['category']] ?? 0) + 1;
            }
        }

        // Calculate average if we have data
        if ($metrics['count'] > 0) {
            $metrics['average'] = $metrics['sum'] / $metrics['count'];
        }

        // Add summary to results
        $results['_summary'] = [
            'metrics' => $metrics,
            'categories' => $categories,
            'names' => array_unique($names),
            'values' => $values,
        ];

        return $results;
    }
}