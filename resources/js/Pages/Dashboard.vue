<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const exampleValue = ref('');
const componentStatus = ref('');
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Plataforma ISAE</CardTitle>
                        <CardDescription>
                            Componentes base de interfaz listos para usar.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <Label for="component-example">Ejemplo</Label>
                        <Input
                            id="component-example"
                            v-model="exampleValue"
                            placeholder="Escribí un valor"
                        />
                    </CardContent>
                    <CardFooter class="flex-col items-start gap-3">
                        <Button
                            type="button"
                            @click="componentStatus = 'Componentes listos.'"
                        >
                            Confirmar
                        </Button>
                        <p v-if="componentStatus" class="text-sm text-muted-foreground">
                            {{ componentStatus }}
                        </p>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
