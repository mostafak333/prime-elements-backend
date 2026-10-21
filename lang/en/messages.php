<?php

return [
    'success' => 'Success',
    'error' => 'Error',
    'created' => 'Created',

    'auth' => [
        'login_successful' => 'Login successful',
        'logout_successful' => 'User logged out successfully',
        'admin_login_successful' => 'Admin login successful',
        'admin_logout_successful' => 'Admin logged out successfully',
        'registration_successful' => 'Registration successful. Please verify your email.',
        'email_verified' => 'Email verified successfully',
        'password_set' => 'Password set successfully',
        'password_reset' => 'Password reset successfully',
        'password_changed' => 'Password changed successfully',
        'password_reset_link_sent' => 'If the email exists, a password reset link has been sent',
        'credentials_invalid' => 'The provided credentials do not match our records.',
        'account_blocked' => 'Your account has been blocked. Please contact support.',
        'account_inactive' => 'Your account is inactive. Please contact the administrator.',
        'email_not_verified' => 'email not verified, please verify your email before logging in.',
        'unauthenticated' => 'Unauthenticated.',
        'session_ended' => 'Your session has been ended. Please log in again.',
        'current_password_incorrect' => 'The current password is incorrect.',
        'invitation_token_invalid' => 'The invitation token is invalid or has expired.',
        'password_reset_token_invalid' => 'The password reset token is invalid or has expired.',
        'verification_token_invalid' => 'The verification token is invalid or has expired.',
    ],

    'admin' => [
        'invitation_sent' => 'Admin invitation sent successfully',
        'list_retrieved' => 'Users retrieved successfully.',
        'retrieved' => 'Admin retrieved successfully.',
        'updated' => 'Admin updated successfully.',
        'status_updated' => 'Admin status updated successfully.',
        'not_found' => 'Admin not found.',
        'update_failed' => 'Failed to update admin: :message',
        'only_super_admin_can_create' => 'Only super admins can create new admin users.',
        'only_super_admin_can_update' => 'Only super admins can update admin users.',
        'cannot_remove_own_super_privileges' => 'You cannot remove your own super admin privileges.',
        'cannot_deactivate_own_account' => 'You cannot deactivate your own account.',
        'role_not_exists' => 'The role ":role" does not exist.',
    ],

    'admin_role' => [
        'retrieved' => 'Admin roles retrieved successfully.',
        'updated' => 'Admin roles updated successfully.',
        'role_not_exists' => 'The specified role does not exist.',
        'role_not_assigned' => 'The admin does not have this role.',
        'role_removed' => 'Role removed from admin successfully.',
    ],

    'category' => [
        'retrieved' => 'Categories retrieved successfully.',
        'retrieved_single' => 'Category retrieved successfully.',
        'created' => 'Category created successfully.',
        'updated' => 'Category updated successfully.',
        'deleted' => 'Category deleted successfully.',
        'cannot_delete' => 'Cannot delete this category.',
        'has_children' => 'Cannot delete a category that has child categories.',
        'has_products' => 'Cannot delete a category that has products. Move or deactivate its products first.',
    ],

    'title' => [
        'created' => 'Title created successfully',
        'updated' => 'Title updated successfully',
        'deleted' => 'Title deleted successfully',
        'cannot_delete' => 'Cannot delete title because it is linked to categories.',
    ],

    'product' => [
        'retrieved' => 'Products retrieved successfully.',
        'retrieved_single' => 'Product retrieved successfully.',
        'created' => 'Product created successfully.',
        'updated' => 'Product updated successfully.',
        'deleted' => 'Product deleted successfully.',
        'cannot_delete' => 'Cannot delete this product.',
        'referenced_by_orders' => 'Cannot delete a product that is referenced by orders. Deactivate it instead.',
    ],

    'delivery_method' => [
        'retrieved' => 'Delivery methods retrieved successfully.',
        'retrieved_single' => 'Delivery method retrieved successfully.',
        'created' => 'Delivery method created successfully.',
        'updated' => 'Delivery method updated successfully.',
        'deleted' => 'Delivery method deleted successfully.',
    ],

    'payment_method' => [
        'retrieved' => 'Payment methods retrieved successfully.',
        'retrieved_single' => 'Payment method retrieved successfully.',
        'created' => 'Payment method created successfully.',
        'updated' => 'Payment method updated successfully.',
        'deleted' => 'Payment method deleted successfully.',
    ],

    'faq' => [
        'retrieved' => 'FAQs retrieved successfully.',
        'retrieved_single' => 'FAQ retrieved successfully.',
        'created' => 'FAQ created successfully.',
        'updated' => 'FAQ updated successfully.',
        'deleted' => 'FAQ deleted successfully.',
        'status_updated' => 'FAQ status updated successfully.',
    ],

    'banner' => [
        'retrieved' => 'Banners retrieved successfully.',
        'retrieved_single' => 'Banner retrieved successfully.',
        'created' => 'Banner created successfully.',
        'updated' => 'Banner updated successfully.',
        'deleted' => 'Banner deleted successfully.',
    ],

    'order' => [
        'retrieved' => 'Orders retrieved successfully.',
        'retrieved_single' => 'Order retrieved successfully.',
        'placed' => 'Order placed successfully.',
        'cancelled' => 'Order cancelled successfully.',
        'status_updated' => 'Order status updated successfully.',
        'not_found' => 'Order not found.',
        'cannot_cancel' => 'Cannot cancel this order.',
        'cannot_cancel_status' => 'Cannot cancel this order because its status is ":status". Only pending orders can be cancelled.',
        'invalid_status_transition' => 'Cannot change status from ":from" to ":to".',
        'cart_empty' => 'Your cart is empty. Add items before placing an order.',
        'account_blocked' => 'Your account has been blocked. You cannot perform order actions.',
        'insufficient_stock' => 'Insufficient stock for ":product". Available: :available, requested: :requested.',
    ],

    'user' => [
        'retrieved' => 'Users retrieved successfully.',
        'retrieved_single' => 'User retrieved successfully.',
        'status_updated' => 'User status updated successfully.',
        'not_found' => 'User not found.',
    ],

    'role' => [
        'retrieved' => 'Roles retrieved successfully.',
        'retrieved_single' => 'Role retrieved successfully.',
        'created' => 'Role created successfully.',
        'updated' => 'Role updated successfully.',
        'deleted' => 'Role deleted successfully.',
        'super_admin_immutable' => 'The SuperAdmin role cannot be modified.',
        'super_admin_not_deletable' => 'The SuperAdmin role cannot be deleted.',
    ],

    'permission' => [
        'retrieved' => 'Permissions retrieved successfully.',
        'created' => 'Permission created successfully.',
        'deleted' => 'Permission deleted successfully.',
        'assigned_to_roles' => 'Cannot delete a permission that is assigned to roles.',
    ],

    'review' => [
        'retrieved' => 'Reviews retrieved successfully.',
        'retrieved_single' => 'Review retrieved successfully.',
        'submitted' => 'Review submitted successfully.',
        'status_updated' => 'Review status updated successfully.',
        'not_eligible' => 'You can review this product after completing your order.',
    ],

    'settings' => [
        'retrieved' => 'Settings retrieved successfully.',
        'updated' => 'Settings updated successfully.',
    ],

    'policy' => [
        'retrieved' => 'Policies retrieved successfully.',
    ],

    'cart' => [
        'retrieved' => 'Cart retrieved successfully.',
        'item_added' => 'Product added to cart successfully.',
        'item_updated' => 'Cart item updated successfully.',
        'item_removed' => 'Cart item removed successfully.',
        'cleared' => 'Cart cleared successfully.',
        'item_not_owned' => 'This cart item does not belong to you.',
    ],

    'wishlist' => [
        'retrieved' => 'Wishlist retrieved successfully.',
        'item_added' => 'Product added to wishlist successfully.',
        'item_removed' => 'Product removed from wishlist successfully.',
        'item_not_owned' => 'This wishlist item does not belong to you.',
    ],

    'profile' => [
        'retrieved' => 'Profile retrieved successfully.',
        'updated' => 'Profile updated successfully.',
    ],

    'newsletter' => [
        'subscribed' => 'Subscribed successfully.',
        'unsubscribed' => 'Subscription preference updated.',
    ],

    'contact' => [
        'message_sent' => 'Your message has been sent successfully.',
    ],

    'social' => [
        'unsupported_provider' => "Provider ':provider' is not supported.",
        'email_unavailable' => 'Unable to retrieve email from the social provider.',
    ],

    'ebook' => [
        'invalid_download_link' => 'Invalid or expired download link.',
    ],
];
