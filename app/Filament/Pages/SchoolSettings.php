<?php
namespace App\Filament\Pages;

use App\Models\SchoolSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class SchoolSettings extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.school-settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string { return __('Paramètres'); }
    public static function getNavigationLabel(): string  { return __('Établissement'); }
    public function getTitle(): string                   { return __('Paramètres de l\'établissement'); }

    public string $school_name   = '';
    public string $slogan        = '';
    public string $description   = '';
    public string $address       = '';
    public string $city          = '';
    public string $country       = 'Tunisie';
    public string $phone         = '';
    public string $mobile        = '';
    public string $email         = '';
    public string $website       = '';
    public string $facebook      = '';
    public string $instagram     = '';
    public string $linkedin      = '';
    public string $youtube       = '';
    public string $academic_year = '';
    public string $school_type   = '';
    public $logo    = null;
    public $favicon = null;

    public ?string $existing_logo    = null;
    public ?string $existing_favicon = null;

    public function mount(): void
    {
        $settings = SchoolSetting::getInstance();
        $this->fill([
            'school_name'   => $settings->school_name   ?? '',
            'slogan'        => $settings->slogan        ?? '',
            'description'   => $settings->description   ?? '',
            'address'       => $settings->address       ?? '',
            'city'          => $settings->city          ?? '',
            'country'       => $settings->country       ?? 'Tunisie',
            'phone'         => $settings->phone         ?? '',
            'mobile'        => $settings->mobile        ?? '',
            'email'         => $settings->email         ?? '',
            'website'       => $settings->website       ?? '',
            'facebook'      => $settings->facebook      ?? '',
            'instagram'     => $settings->instagram     ?? '',
            'linkedin'      => $settings->linkedin      ?? '',
            'youtube'       => $settings->youtube       ?? '',
            'academic_year' => $settings->academic_year ?? '',
            'school_type'   => $settings->school_type   ?? '',
        ]);
        $this->existing_logo    = $settings->logo;
        $this->existing_favicon = $settings->favicon;
    }

    public function save(): void
    {
        $settings = SchoolSetting::getInstance();

        $data = [
            'school_name'   => $this->school_name,
            'slogan'        => $this->slogan,
            'description'   => $this->description,
            'address'       => $this->address,
            'city'          => $this->city,
            'country'       => $this->country,
            'phone'         => $this->phone,
            'mobile'        => $this->mobile,
            'email'         => $this->email,
            'website'       => $this->website,
            'facebook'      => $this->facebook,
            'instagram'     => $this->instagram,
            'linkedin'      => $this->linkedin,
            'youtube'       => $this->youtube,
            'academic_year' => $this->academic_year,
            'school_type'   => $this->school_type,
        ];

        if ($this->logo) {
            $path = $this->logo->store('school', 'public');
            $data['logo'] = $path;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('school', 'public');
            $data['favicon'] = $path;
        }

        $settings->update($data);

        Notification::make()->success()
            ->title(__('Paramètres sauvegardés'))
            ->body(__('Les informations de l\'établissement ont été mises à jour.'))
            ->send();
    }
}
