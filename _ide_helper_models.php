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
 * @property string $activity_type
 * @property string|null $model_type
 * @property int|null $model_id
 * @property array|null $before_data
 * @property array|null $after_data
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $logged_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereActivityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereAfterData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereBeforeData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereLoggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AuditTrail withoutTrashed()
 */
	class AuditTrail extends \Eloquent {}
}

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
 * @property string|null $group
 * @property string $cost_center
 * @property string $cost_code
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\userGeneric> $userGeneric
 * @property-read int|null $user_generic_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\userNIK> $userNIK
 * @property-read int|null $user_n_i_k_count
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter withoutTrashed()
 */
	class CostCenter extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_code
 * @property string $user_name
 * @property string $cost_code
 * @property string|null $dokumen_keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\userGeneric|null $genericUser
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereCostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereDokumenKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereUserCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCurrentUser withoutTrashed()
 */
	class CostCurrentUser extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_code
 * @property string $user_name
 * @property string $cost_code
 * @property string|null $dokumen_keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\userGeneric|null $genericUser
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereCostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereDokumenKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereUserCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CostPrevUser withoutTrashed()
 */
	class CostPrevUser extends \Eloquent {}
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
 * @property-read \App\Models\CompositeRole|null $compositeRole
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

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nama
 * @property string $nik
 * @property int|null $company_id
 * @property string|null $direktorat
 * @property int|null $kompartemen_id
 * @property int|null $departemen_id
 * @property string|null $email
 * @property string|null $grade
 * @property string|null $jabatan
 * @property string|null $atasan
 * @property string|null $cost_center
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostCenter> $costCenter
 * @property-read int|null $cost_center_count
 * @property-read \App\Models\Departemen|null $departemen
 * @property-read \App\Models\Kompartemen|null $kompartemen
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\userNIK|null $userNIK
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereAtasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereDepartemenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereDirektorat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereKompartemenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDetail withoutTrashed()
 */
	class UserDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $license_type
 * @property string $contract_license_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property-read \App\Models\User|null $User
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereContractLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLicenseManagement withoutTrashed()
 */
	class UserLicenseManagement extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_code
 * @property string $user_type
 * @property string $cost_code
 * @property string $license_type
 * @property string|null $group
 * @property string|null $valid_from
 * @property string|null $valid_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read \App\Models\Company|null $Company
 * @property-read \App\Models\CostCenter|null $costCenter
 * @property-read \App\Models\CostCurrentUser|null $currentUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostPrevUser> $prevUser
 * @property-read int|null $prev_user_count
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric query()
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereCostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereUserCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric whereValidTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|userGeneric withoutTrashed()
 */
	class userGeneric extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $user_code
 * @property string $user_type
 * @property string $license_type
 * @property string|null $valid_from
 * @property string|null $valid_to
 * @property string|null $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property-read \App\Models\Company|null $Company
 * @property-read \App\Models\UserDetail|null $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK query()
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereLicenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereUserCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK whereValidTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|userNIK withoutTrashed()
 */
	class userNIK extends \Eloquent {}
}

