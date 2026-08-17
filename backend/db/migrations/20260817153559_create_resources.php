<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateResources extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('resources');
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->create();

        $table
            ->insert([
                ['name' => 'Conference Room A'],
                ['name' => 'Conference Room B'],
                ['name' => 'Projector'],
            ])
            ->saveData();
    }
}
