<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(3);
$totalPages = $pager->getPageCount();
$currentPage = $pager->getCurrentPageNumber();
?>

<nav aria-label="分页导航" class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">
            共 <strong><?= $totalPages ?></strong> 页，当前第 <strong><?= $currentPage ?></strong> 页
        </div>
    </div>
    <ul class="pagination justify-content-center pagination-lg">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="首页">
                    <span aria-hidden="true"><i class="fas fa-home"></i></span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPreviousPage() ?>" aria-label="上一页">
                    <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                    <span aria-hidden="true"><i class="fas fa-home"></i></span>
                </a>
            </li>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                    <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNextPage() ?>" aria-label="下一页">
                    <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="末页">
                    <span aria-hidden="true"><i class="fas fa-flag"></i></span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                    <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </a>
            </li>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                    <span aria-hidden="true"><i class="fas fa-flag"></i></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
    <style>
        .pagination {
            margin-top: 1rem;
        }
        .page-link {
            transition: all 0.3s ease;
        }
        .page-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        @media (max-width: 576px) {
            .pagination {
                font-size: 0.875rem;
            }
            .page-link {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
</nav>