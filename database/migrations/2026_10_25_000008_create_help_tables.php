<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🆘 Centro de ayuda: desarrolladores, manuales, tutoriales y soporte.
 * Datos configurables por administración; sin datos ficticios.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Perfiles de desarrolladores (se muestran solo si el admin los publica)
        Schema::create('help_developers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('bio')->nullable();
            $table->string('specialty')->nullable();
            $table->string('avatar')->nullable();
            $table->string('email')->nullable();
            $table->json('social_links')->nullable()->comment('[{network,url}] validados en servidor');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Manuales de usuario
        Schema::create('help_manuals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('module')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('attachment')->nullable()->comment('PDF u otro documento');
            $table->string('status')->default('published')->comment('published|draft|archived');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Tutoriales en vídeo
        Schema::create('help_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->string('video_url')->nullable()->comment('Proveedor admitido o archivo propio');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Solicitudes de soporte técnico
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('category')->default('otro');
            $table->string('subject');
            $table->text('description');
            $table->string('status', 16)->default('open')->comment('open|answered|closed');
            $table->string('tracking_code', 12)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
        Schema::dropIfExists('help_tutorials');
        Schema::dropIfExists('help_manuals');
        Schema::dropIfExists('help_developers');
    }
};
