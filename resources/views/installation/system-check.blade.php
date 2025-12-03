@extends('layouts.installation')

@section('title', 'System Requirements Check')

@section('content')
<div class="step active">
    <h3>🔍 System Requirements Check</h3>
    <p>Checking your system compatibility with Laravel requirements...</p>

    <div class="system-info">
        <h4>📋 System Information</h4>
        <div class="info-grid">
            <div><strong>OS:</strong> {{ $systemInfo['os'] }}</div>
            <div><strong>PHP SAPI:</strong> {{ $systemInfo['php_sapi'] }}</div>
            <div><strong>Memory Limit:</strong> {{ $systemInfo['memory_limit'] }}</div>
            <div><strong>Max Execution Time:</strong> {{ $systemInfo['max_execution_time'] }}s</div>
        </div>
    </div>

    <h4>🔧 PHP Requirements</h4>
    @foreach($phpCheck['details'] as $name => $req)
        <div class="requirement-check {{ $req['status'] ? 'passed' : 'failed' }}">
            <div class="requirement-name">
                {{ ucfirst(str_replace('_', ' ', $name)) }}
            </div>
            <div class="requirement-value">
                {{ $req['current'] }}
                {{ $req['status'] ? '✅' : '❌' }}
            </div>
        </div>
    @endforeach

    <h4 style="margin-top: 20px;">📦 Development Tools</h4>
    <div class="requirement-check {{ $composerCheck['installed'] ? 'passed' : 'failed' }}">
        <div class="requirement-name">Composer</div>
        <div class="requirement-value">
            {{ $composerCheck['installed'] ? 'v' . $composerCheck['version'] . ' ✅' : 'Not installed ❌' }}
        </div>
    </div>

    <div class="requirement-check {{ $nodeCheck['node_installed'] ? 'passed' : 'failed' }}">
        <div class="requirement-name">Node.js</div>
        <div class="requirement-value">
            {{ $nodeCheck['node_installed'] ? 'v' . $nodeCheck['node_version'] . ' ' . ($nodeCheck['node_version_ok'] ? '✅' : '⚠️') : 'Not installed ❌' }}
        </div>
    </div>

    <div class="requirement-check {{ $nodeCheck['npm_installed'] ? 'passed' : 'failed' }}">
        <div class="requirement-name">NPM</div>
        <div class="requirement-value">
            {{ $nodeCheck['npm_installed'] ? 'v' . $nodeCheck['npm_version'] . ' ✅' : 'Not installed ❌' }}
        </div>
    </div>

    <h4 style="margin-top: 20px;">📁 Laravel Project Structure</h4>
    @foreach($laravelCheck as $name => $status)
        <div class="requirement-check {{ $status ? 'passed' : 'failed' }}">
            <div class="requirement-name">
                {{ ucfirst(str_replace('_', ' ', $name)) }}
            </div>
            <div class="requirement-value">
                {{ $status ? 'Found ✅' : 'Missing ❌' }}
            </div>
        </div>
    @endforeach

    @php
        $canProceed = $phpCheck['passed'] && $composerCheck['installed'] && $nodeCheck['node_installed'] && $nodeCheck['npm_installed'];
        $allLaravelFilesExist = $laravelCheck['composer_json'] && $laravelCheck['artisan'] && $laravelCheck['app_directory'];
    @endphp

    @if($canProceed && $allLaravelFilesExist)
        <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 5px; color: #155724;">
            <strong>✅ All system requirements are met!</strong><br>
            You can proceed with the Laravel setup.
        </div>
        <form method="POST" action="{{ route('installation.proceed-setup') }}" style="margin-top: 15px;">
            @csrf
            <button type="submit" class="btn">Proceed with Setup</button>
        </form>
    @else
        <div style="margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 5px; color: #721c24;">
            <strong>❌ System requirements not met!</strong><br>
            Please install the missing requirements before proceeding.
        </div>
    @endif

    @if(file_exists(base_path('.env')))
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; border: 1px solid #ffeaa7;">
            <strong>⚠️ Existing Configuration Found</strong><br>
            <p style="font-size: 12px; margin: 5px 0;">An .env file already exists. You can reset to start fresh.</p>
            <form method="POST" action="{{ route('installation.reset-setup') }}" style="margin-top: 10px;"
                  onsubmit="return confirm('This will delete your current .env file and restart setup. Continue?')">
                @csrf
                <button type="submit" class="btn btn-danger" style="font-size: 14px; padding: 8px 16px;">
                    🔄 Reset Configuration
                </button>
            </form>
        </div>
    @endif
</div>

<div class="info-box">
    <strong>Laravel Setup Wizard</strong>
    This wizard will guide you through the complete Laravel project setup process including:
    <ul>
        <li>Composer package installation</li>
        <li>NPM package installation and asset building</li>
        <li>Environment configuration</li>
        <li>Database setup and migration</li>
        <li>Application key generation</li>
        <li>Storage link creation</li>
    </ul>
</div>
@endsection
