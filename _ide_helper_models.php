<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $company_code
 * @property string $name
 * @property string|null $shortname
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Departemen> $departemen
 * @property-read int|null $departemen_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Departemen> $departemenWithoutKompartemen
 * @property-read int|null $departemen_without_kompartemen_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRole> $jobRoles
 * @property-read int|null $job_roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRole> $jobRolesWithoutRelations
 * @property-read int|null $job_roles_without_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kompartemen> $kompartemen
 * @property-read int|null $kompartemen_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCompanyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereShortname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Company withoutTrashed()
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $nama
 * @property string|null $deskripsi
 * @property int|null $jabatan_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\JobRole|null $jobRole
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SingleRole> $singleRoles
 * @property-read int|null $single_roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereJabatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CompositeRole withoutTrashed()
 */
	class CompositeRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $company_id
 * @property int|null $kompartemen_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRole> $jobRoles
 * @property-read int|null $job_roles_count
 * @property-read \App\Models\Kompartemen|null $kompartemen
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen query()
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereKompartemenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Departemen withoutTrashed()
 */
	class Departemen extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $nama_jabatan
 * @property string|null $deskripsi
 * @property int|null $kompartemen_id
 * @property int|null $departemen_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompositeRole> $compositeRoles
 * @property-read int|null $composite_roles_count
 * @property-read \App\Models\Departemen|null $departemen
 * @property-read \App\Models\Kompartemen|null $kompartemen
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereDepartemenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereKompartemenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereNamaJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|JobRole withoutTrashed()
 */
	class JobRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Departemen> $departemen
 * @property-read int|null $departemen_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRole> $jobRoles
 * @property-read int|null $job_roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Kompartemen withoutTrashed()
 */
	class Kompartemen extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $nama
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompositeRole> $compositeRoles
 * @property-read int|null $composite_roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tcode> $tcodes
 * @property-read int|null $tcodes_count
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SingleRole withoutTrashed()
 */
	class SingleRole extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $code
 * @property string|null $sap_module
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SingleRole> $singleRoles
 * @property-read int|null $single_roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereSapModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tcode withoutTrashed()
 */
	class Tcode extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method bool hasRole(string $role)
 * @method bool can(string $permission)
 * @method bool hasPermissionTo(string $permission)
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

