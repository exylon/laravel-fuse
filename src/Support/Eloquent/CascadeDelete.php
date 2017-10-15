<?php


namespace Exylon\Fuse\Support\Eloquent;


trait CascadeDelete
{

    /**
     * @var bool
     */
    public $useCascadeDeleteTransaction = true;

    public static function bootCascadeDelete()
    {
        static::deleting(function ($model) {
            if (property_exists($model, 'cascade')
                && is_array($model->cascade)
                && !empty($model->cascade)
            ) {
                //
                // Make sure to have a single transaction
                //
                if ($model->useCascadeDeleteTransaction) {
                    \DB::beginTransaction();
                }

                foreach ($model->cascade as $relation) {
                    if (method_exists($model, $relation)) {
                        //
                        // Get a sample from the database
                        //
                        $first = $model->{$relation}()->first();

                        //
                        // Recurse through cascade-able models
                        //
                        if (has_trait($first, CascadeDelete::class)) {
                            foreach ($model->{$relation}()->get([$first->getKeyName()]) as $related) {

                                //
                                // Avoid recursive delete to self
                                //
                                if ($related !== $model) {
                                    //
                                    // Disable succeeding transaction calls
                                    //
                                    $related->useCascadeDeleteTransaction = false;
                                    $related->delete();
                                }
                            }
                        }
                        //
                        // If the relationship is not cascade-able, delete as a set
                        //
                        else {
                            $model->{$relation}()->delete();
                        }
                    }
                }

                if ($model->useCascadeDeleteTransaction) {
                    \DB::commit();
                }
            }
        });
    }

}
