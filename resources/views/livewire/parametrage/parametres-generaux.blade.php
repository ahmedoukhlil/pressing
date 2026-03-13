<div class="page-container">
    <h1 class="text-2xl font-semibold">Parametres generaux</h1>
    <p class="text-sm text-gray-600 mt-2">Configuration affichee sur les tickets et ecrans.</p>

    <form wire:submit.prevent="sauvegarder" class="mt-6 card space-y-4 max-w-3xl">
        @if (session('success'))
            <div class="rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Nom du pressing *</label>
            <input type="text" wire:model.live="nomPressing" class="w-full rounded border-gray-300">
            @error('nomPressing') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Adresse</label>
            <input type="text" wire:model.live="adresse" class="w-full rounded border-gray-300">
            @error('adresse') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Telephone</label>
            <input type="text" wire:model.live="telephone" class="w-full rounded border-gray-300">
            @error('telephone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Texte bas de ticket</label>
            <input type="text" wire:model.live="footerTicket" class="w-full rounded border-gray-300" placeholder="Merci pour votre confiance">
            @error('footerTicket') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">Enregistrer</button>
        </div>
    </form>
</div>
