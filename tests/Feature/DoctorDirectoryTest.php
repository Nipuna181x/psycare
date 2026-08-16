<?php

namespace Tests\Feature;

use Tests\TestCase;

class DoctorDirectoryTest extends TestCase
{
    public function test_doctor_directory_can_be_viewed(): void
    {
        $response = $this->get(route('doctors.index'));

        $response
            ->assertOk()
            ->assertSee('Book any doctor, any clinic, one calm search')
            ->assertSee('Dr. Anusha Perera')
            ->assertSee('Dr. Mahesh Kulasooriya');
    }
}
