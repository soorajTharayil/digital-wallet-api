

<?php $__env->startSection('title', 'Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="px-4 py-8 sm:px-0">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
            <a href="<?php echo e(route('dashboard')); ?>" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="<?php echo e(route('transactions.index')); ?>" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                    <div class="flex-1">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="type" 
                                name="type" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All</option>
                            <option value="credit" <?php echo e(request('type') == 'credit' ? 'selected' : ''); ?>>Credit</option>
                            <option value="debit" <?php echo e(request('type') == 'debit' ? 'selected' : ''); ?>>Debit</option>
                        </select>
                    </div>

                    <div class="flex-1">
                        <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                        <select id="currency" 
                                name="currency" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All</option>
                            <option value="USD" <?php echo e(request('currency') == 'USD' ? 'selected' : ''); ?>>USD</option>
                            <option value="INR" <?php echo e(request('currency') == 'INR' ? 'selected' : ''); ?>>INR</option>
                            <option value="EUR" <?php echo e(request('currency') == 'EUR' ? 'selected' : ''); ?>>EUR</option>
                        </select>
                    </div>

                    <div class="flex-1">
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                        <select id="per_page" 
                                name="per_page" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="20" <?php echo e(request('per_page', 20) == 20 ? 'selected' : ''); ?>>20</option>
                            <option value="50" <?php echo e(request('per_page') == 50 ? 'selected' : ''); ?>>50</option>
                            <option value="100" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <?php if(isset($error)): ?>
                    <div class="text-center py-8">
                        <p class="text-red-600"><?php echo e($error); ?></p>
                    </div>
                <?php elseif(isset($transactions) && count($transactions) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo e($transaction['type'] === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo e(ucfirst($transaction['type'])); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e(number_format($transaction['amount'], 2)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($transaction['currency']); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo e($transaction['description'] ?? 'N/A'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e(\Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y H:i')); ?>

                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if(isset($pagination) && $pagination['last_page'] > 1): ?>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?php echo e((($pagination['current_page'] - 1) * $pagination['per_page']) + 1); ?> 
                            to <?php echo e(min($pagination['current_page'] * $pagination['per_page'], $pagination['total'])); ?> 
                            of <?php echo e($pagination['total']); ?> results
                        </div>
                        <div class="flex space-x-2">
                            <?php if($pagination['current_page'] > 1): ?>
                                <a href="<?php echo e(route('transactions.index', array_merge($filters ?? [], ['page' => $pagination['current_page'] - 1]))); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>
                            <?php if($pagination['current_page'] < $pagination['last_page']): ?>
                                <a href="<?php echo e(route('transactions.index', array_merge($filters ?? [], ['page' => $pagination['current_page'] + 1]))); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500">No transactions found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Desktop\wallet-api\resources\views/transactions/index.blade.php ENDPATH**/ ?>