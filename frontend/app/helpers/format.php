<?php

function formatStatut(string $statut): string
{
    $map = [
        'a_venir'       => 'À venir',
        'en_cours'      => 'En cours',
        'termine'       => 'Terminé',
        'terminee'      => 'Terminée',
        'valide'        => 'Validé',
        'validee'       => 'Validée',
        'en_attente'    => 'En attente',
        'rejete'        => 'Rejeté',
        'rejetee'       => 'Rejetée',
        'refuse'        => 'Refusé',
        'refusee'       => 'Refusée',
        'recupere'      => 'Récupéré',
        'reserve_pro'   => 'Réservé',
        'en_stock'      => 'En stock',
        'paye'          => 'Payé',
        'payee'         => 'Payée',
        'impaye'        => 'Impayé',
        'impayee'       => 'Impayée',
        'annule'        => 'Annulé',
        'annulee'       => 'Annulée',
        'publie'        => 'Publié',
        'publiee'       => 'Publiée',
        'vendue'        => 'Vendue',
        'brouillon'     => 'Brouillon',
        'disponible'    => 'Disponible',
        'presque_plein' => 'Presque plein',
        'plein'         => 'Plein',
        'pleine'        => 'Pleine',
        'maintenance'   => 'Maintenance',
        'hors_service'  => 'Hors service',
        'actif'         => 'Actif',
        'inactif'       => 'Inactif',
        'suspendu'      => 'Suspendu',
        'pause'         => 'En pause',
        'rouvert'       => 'Rouvert',
    ];
    return $map[$statut] ?? ucfirst(str_replace('_', ' ', $statut));
}

function formatDate(?string $dateStr, bool $withTime = false): string
{
    if (empty($dateStr) || str_starts_with($dateStr, '0000-00-00')) {
        return '—';
    }
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return $dateStr;
    }
    return $withTime ? date('d/m/Y à H\hi', $ts) : date('d/m/Y', $ts);
}

function formatPrix($prix): string
{
    $p = (float) $prix;
    return $p == 0.0 ? 'Gratuit' : number_format($p, 2, ',', ' ') . ' €';
}

function statutCouleur(string $statut): string
{
    $map = [
        'valide'        => '#22c55e',
        'validee'       => '#22c55e',
        'payee'         => '#22c55e',
        'paye'          => '#22c55e',
        'disponible'    => '#22c55e',
        'recupere'      => '#22c55e',
        'actif'         => '#22c55e',
        'vendue'        => '#22c55e',
        'a_venir'       => '#3b82f6',
        'en_cours'      => '#3b82f6',
        'publie'        => '#3b82f6',
        'publiee'       => '#3b82f6',
        'reserve_pro'   => '#3b82f6',
        'en_stock'      => '#3b82f6',
        'en_attente'    => '#f59e0b',
        'presque_plein' => '#f59e0b',
        'pause'         => '#f59e0b',
        'suspendu'      => '#f59e0b',
        'brouillon'     => '#94a3b8',
        'termine'       => '#94a3b8',
        'terminee'      => '#94a3b8',
        'inactif'       => '#94a3b8',
        'rejete'        => '#ef4444',
        'rejetee'       => '#ef4444',
        'refuse'        => '#ef4444',
        'refusee'       => '#ef4444',
        'impayee'       => '#ef4444',
        'impaye'        => '#ef4444',
        'annule'        => '#ef4444',
        'annulee'       => '#ef4444',
        'plein'         => '#ef4444',
        'pleine'        => '#ef4444',
        'maintenance'   => '#ef4444',
        'hors_service'  => '#ef4444',
    ];
    return $map[$statut] ?? '#94a3b8';
}
